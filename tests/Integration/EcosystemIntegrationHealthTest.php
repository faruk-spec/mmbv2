<?php

require_once BASE_PATH . '/core/Autoloader.php';

use Core\EcosystemIntegration;

class EcosystemIntegrationHealthTest extends TestCase
{
    public function testWaveOneRoutesResolve(): void
    {
        $this->assertNotEmpty(EcosystemIntegration::route('qr_generate'));
        $this->assertNotEmpty(EcosystemIntegration::route('linkshortner_create'));
        $this->assertNotEmpty(EcosystemIntegration::route('proshare_preview', ['short_code' => 'abc123']));
        $this->assertNotEmpty(EcosystemIntegration::route('formx_public', ['slug' => 'contact-us']));
    }

    public function testWaveOneEntityActionsResolveForHandoffs(): void
    {
        $qrActions = EcosystemIntegration::actionsForEntity('qr_url', [
            'content_url' => 'https://example.com',
        ]);
        $this->assertNotEmpty($qrActions);

        $formActions = EcosystemIntegration::actionsForEntity('formx_form', [
            'public_url' => 'https://example.com/forms/contact-us',
        ]);
        $this->assertNotEmpty($formActions);

        $fileActions = EcosystemIntegration::actionsForEntity('proshare_file', [
            'public_url' => 'https://example.com/proshare/preview/abc123',
        ]);
        $this->assertNotEmpty($fileActions);
    }
}
