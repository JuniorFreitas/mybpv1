import { test, expect } from '@playwright/test';
import { fetchWhatsappStatus } from './helpers';

test.describe('API — status WhatsApp', () => {
    test('GET /g/configuracoes/whatsapp/status retorna estrutura esperada', async ({ request }) => {
        const status = await fetchWhatsappStatus(request);

        expect(status).toHaveProperty('whatsapp_liberado');
        expect(status).toHaveProperty('modulos');
        expect(status).toHaveProperty('tipos');
        expect(status).toHaveProperty('empresa_id');
        expect(typeof status.empresa_id).toBe('number');
        expect(typeof status.whatsapp_liberado).toBe('boolean');
    });

    test('tipos só true se empresa liberada e módulo habilitado', async ({ request }) => {
        const status = await fetchWhatsappStatus(request);

        if (!status.whatsapp_liberado) {
            for (const habilitado of Object.values(status.tipos)) {
                expect(habilitado).toBe(false);
            }
        }
    });
});
