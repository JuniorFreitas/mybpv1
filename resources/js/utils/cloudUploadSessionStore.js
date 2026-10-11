const DB_NAME = 'mybp-cloud-upload';
const DB_VERSION = 1;
const STORE = 'sessions';

function openDb() {
  return new Promise((resolve, reject) => {
    if (typeof indexedDB === 'undefined') {
      reject(new Error('IndexedDB indisponível'));
      return;
    }
    const req = indexedDB.open(DB_NAME, DB_VERSION);
    req.onupgradeneeded = () => {
      const db = req.result;
      if (!db.objectStoreNames.contains(STORE)) {
        db.createObjectStore(STORE, { keyPath: 'id' });
      }
    };
    req.onsuccess = () => resolve(req.result);
    req.onerror = () => reject(req.error || new Error('Falha ao abrir IndexedDB'));
  });
}

function sessionId({ cloudId, pertenceId, nome, size, lastModified }) {
  return `c${cloudId || 0}_p${pertenceId || 0}_${nome}_${size}_${lastModified}`;
}

export function buildCloudUploadSessionKey(meta) {
  return sessionId(meta);
}

export async function saveCloudUploadSession(session) {
  const db = await openDb();
  return new Promise((resolve, reject) => {
    const tx = db.transaction(STORE, 'readwrite');
    tx.objectStore(STORE).put(session);
    tx.oncomplete = () => resolve();
    tx.onerror = () => reject(tx.error);
  });
}

export async function getCloudUploadSession(id) {
  const db = await openDb();
  return new Promise((resolve, reject) => {
    const tx = db.transaction(STORE, 'readonly');
    const req = tx.objectStore(STORE).get(id);
    req.onsuccess = () => resolve(req.result || null);
    req.onerror = () => reject(req.error);
  });
}

export async function deleteCloudUploadSession(id) {
  const db = await openDb();
  return new Promise((resolve, reject) => {
    const tx = db.transaction(STORE, 'readwrite');
    tx.objectStore(STORE).delete(id);
    tx.oncomplete = () => resolve();
    tx.onerror = () => reject(tx.error);
  });
}

export async function listCloudUploadSessionsForCloud(cloudId, pertenceId) {
  const db = await openDb();
  return new Promise((resolve, reject) => {
    const tx = db.transaction(STORE, 'readonly');
    const req = tx.objectStore(STORE).getAll();
    req.onsuccess = () => {
      const all = req.result || [];
      resolve(
        all.filter(
          (s) =>
            String(s.cloudId) === String(cloudId) &&
            String(s.pertenceId || '') === String(pertenceId || '') &&
            !s.completed
        )
      );
    };
    req.onerror = () => reject(req.error);
  });
}

export async function discardCloudUploadSessions(sessions) {
  if (!Array.isArray(sessions) || !sessions.length) {
    return;
  }
  await Promise.all(sessions.map((s) => (s && s.id ? deleteCloudUploadSession(s.id) : Promise.resolve())));
}
