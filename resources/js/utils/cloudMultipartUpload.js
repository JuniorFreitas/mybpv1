import {
  buildCloudUploadSessionKey,
  deleteCloudUploadSession,
  getCloudUploadSession,
  saveCloudUploadSession,
} from './cloudUploadSessionStore';

const DEFAULT_CONCURRENCY = 3;

function http() {
  // Usa o axios global (CSRF/interceptors do bootstrap). Fallback para import dinâmico não necessário.
  if (typeof window !== 'undefined' && window.axios) {
    return window.axios;
  }
  // eslint-disable-next-line global-require
  return require('axios');
}

function normalizeEtag(etag) {
  if (!etag) {
    return '';
  }
  return String(etag).replace(/^"|"$/g, '');
}

async function mapPool(items, concurrency, worker) {
  const results = new Array(items.length);
  let next = 0;

  async function run() {
    while (next < items.length) {
      const index = next;
      next += 1;
      results[index] = await worker(items[index], index);
    }
  }

  const runners = Array.from({ length: Math.min(concurrency, items.length) }, () => run());
  await Promise.all(runners);
  return results;
}

function metaSemBlob(session) {
  if (!session) {
    return session;
  }
  const { fileBlob, ...meta } = session;
  return meta;
}

async function persistirSessao(session) {
  // Nunca grava o File/Blob no IndexedDB — arquivos grandes travam o browser.
  try {
    await saveCloudUploadSession(metaSemBlob(session));
  } catch (_) {
    /* quota / privado: upload continua */
  }
}

/**
 * Upload multipart híbrido (S3 presigned ou partes via Laravel).
 * Retomada após F5: metadados/partes no IndexedDB; o File precisa ser re-selecionado.
 */
export async function uploadCloudMultipart(options) {
  const {
    file,
    urls,
    dadosAjax = {},
    onProgress,
    signal,
    resumeSession = null,
    concurrency = DEFAULT_CONCURRENCY,
  } = options;

  const api = http();

  const idbKey = buildCloudUploadSessionKey({
    cloudId: dadosAjax.cloud_id,
    pertenceId: dadosAjax.pertence_id,
    nome: file.name,
    size: file.size,
    lastModified: file.lastModified,
  });

  let session = resumeSession || (await getCloudUploadSession(idbKey));
  let uploadSessionId;
  let partSize;
  let modo;
  let partesFeitas = {};

  const iniciarNovaSessao = async (partesIniciais = {}) => {
    const { data } = await api.post(urls.iniciar, {
      cloud_id: dadosAjax.cloud_id,
      pertence_id: dadosAjax.pertence_id || null,
      anterior_id: dadosAjax.anterior_id || null,
      nome_original: file.name,
      mime: file.type || 'application/octet-stream',
      bytes: file.size,
    });

    uploadSessionId = data.upload_session_id;
    partSize = data.part_size;
    modo = data.modo;
    partesFeitas = { ...partesIniciais };

    session = {
      id: idbKey,
      cloudId: dadosAjax.cloud_id,
      pertenceId: dadosAjax.pertence_id || null,
      anteriorId: dadosAjax.anterior_id || null,
      uploadSessionId,
      partSize,
      modo,
      nome: file.name,
      size: file.size,
      lastModified: file.lastModified,
      mime: file.type || 'application/octet-stream',
      partes: { ...partesFeitas },
      completed: false,
      createdAt: Date.now(),
    };
    await persistirSessao(session);
  };

  if (session && session.uploadSessionId && session.partSize) {
    uploadSessionId = session.uploadSessionId;
    partSize = session.partSize;
    modo = session.modo;
    partesFeitas = session.partes || {};
    session = {
      ...session,
      nome: file.name,
      size: file.size,
      lastModified: file.lastModified,
    };
  } else {
    await iniciarNovaSessao();
  }

  const throwIfAborted = () => {
    if (signal && signal.aborted) {
      const err = new Error('Upload cancelado');
      err.canceled = true;
      throw err;
    }
  };

  const totalParts = Math.ceil(file.size / partSize) || 1;
  const partNumbers = Array.from({ length: totalParts }, (_, i) => i + 1);
  let bytesEnviados = Object.keys(partesFeitas).reduce((acc, n) => {
    const num = Number(n);
    if (num < totalParts) {
      return acc + partSize;
    }
    return acc + (file.size - partSize * (totalParts - 1));
  }, 0);

  const report = () => {
    if (typeof onProgress === 'function') {
      onProgress({
        loaded: Math.min(bytesEnviados, file.size),
        total: file.size,
        pct: file.size ? Math.round((Math.min(bytesEnviados, file.size) / file.size) * 100) : 0,
      });
    }
  };
  report();

  const enviarParte = async (partNumber) => {
    throwIfAborted();

    if (partesFeitas[partNumber]) {
      return { part_number: partNumber, etag: partesFeitas[partNumber] };
    }

    const start = (partNumber - 1) * partSize;
    const end = Math.min(start + partSize, file.size);
    const blob = file.slice(start, end);

    let signed;
    try {
      const respSigned = await api.post(urls.assinarParte, {
        upload_session_id: uploadSessionId,
        part_number: partNumber,
      });
      signed = respSigned.data;
    } catch (err) {
      if (err && err.response && err.response.status === 404) {
        // Sessão server expirou: reinicia (perde partes já enviadas no S3 antigo)
        await iniciarNovaSessao();
        bytesEnviados = 0;
        report();
        const respSigned = await api.post(urls.assinarParte, {
          upload_session_id: uploadSessionId,
          part_number: partNumber,
        });
        signed = respSigned.data;
      } else {
        throw err;
      }
    }

    throwIfAborted();

    let etag = '';

    if (signed.modo === 's3') {
      const resp = await fetch(signed.url, {
        method: 'PUT',
        body: blob,
        signal,
      });
      if (!resp.ok) {
        throw new Error(`Falha no upload da parte ${partNumber} (HTTP ${resp.status})`);
      }
      etag = normalizeEtag(resp.headers.get('ETag'));
      if (!etag) {
        throw new Error(
          `ETag ausente na parte ${partNumber}. Configure CORS do bucket S3 com ExposeHeaders: ETag.`
        );
      }
    } else {
      const form = new FormData();
      form.append('upload_session_id', uploadSessionId);
      form.append('part_number', String(partNumber));
      form.append('parte', blob, `part_${partNumber}`);
      const { data } = await api.post(signed.url || urls.parte, form, {
        headers: { 'Content-Type': 'multipart/form-data' },
        signal,
      });
      etag = normalizeEtag(data.etag);
    }

    if (!etag) {
      throw new Error(`ETag ausente na parte ${partNumber}`);
    }

    partesFeitas[partNumber] = etag;
    bytesEnviados += blob.size;
    report();

    session.partes = { ...partesFeitas };
    await persistirSessao(session);

    return { part_number: partNumber, etag };
  };

  try {
    const resultados = await mapPool(partNumbers, concurrency, enviarParte);
    throwIfAborted();

    const partes = resultados
      .filter(Boolean)
      .map((p) => ({
        part_number: p.part_number,
        etag: p.etag.includes('"') ? p.etag : `"${p.etag}"`,
      }))
      .sort((a, b) => a.part_number - b.part_number);

    const partesPayload = partes.map((p) => ({
      part_number: p.part_number,
      etag: modo === 's3' ? p.etag : normalizeEtag(p.etag),
    }));

    const { data: arquivo } = await api.post(urls.completar, {
      upload_session_id: uploadSessionId,
      partes: partesPayload,
    });

    await deleteCloudUploadSession(idbKey);
    return arquivo;
  } catch (error) {
    const cancelado = error && (error.canceled || (signal && signal.aborted));
    if (cancelado) {
      try {
        await api.post(urls.abortar, { upload_session_id: uploadSessionId });
      } catch (_) {
        /* ignore */
      }
      await deleteCloudUploadSession(idbKey);
    }
    throw error;
  }
}

export async function abortCloudMultipartSession(urls, uploadSessionId, idbKey) {
  const api = http();
  try {
    if (uploadSessionId) {
      await api.post(urls.abortar, { upload_session_id: uploadSessionId });
    }
  } catch (_) {
    /* ignore */
  }
  if (idbKey) {
    await deleteCloudUploadSession(idbKey);
  }
}

export function arquivoBateComSessao(file, session) {
  if (!file || !session) {
    return false;
  }
  return (
    file.name === session.nome &&
    file.size === session.size &&
    (!session.lastModified || file.lastModified === session.lastModified)
  );
}
