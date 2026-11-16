<?php

namespace Tests\Feature;

use App\Mail\VerifyEmailMail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Email;
use Tests\TestCase;

class MailConfigTest extends TestCase
{
    public function test_env_example_and_azure_appsettings_define_official_mail_addresses(): void
    {
        $expectedDirectives = [
            'MAIL_FROM_ADDRESS="contato@assin.art"',
            'MAIL_FROM_NAME="${APP_NAME}"',
            'MAIL_REPLY_TO_ADDRESS="sac@assin.art"',
            'MAIL_REPLY_TO_NAME="${APP_NAME}"',
        ];

        $envExample = file_get_contents(base_path('.env.example'));
        $appSettings = file_get_contents(base_path('azure/appsettings.example.env'));

        $this->assertIsString($envExample);
        $this->assertIsString($appSettings);

        foreach ($expectedDirectives as $directive) {
            $this->assertStringContainsString($directive, $envExample);
            $this->assertStringContainsString($directive, $appSettings);
        }
    }

    public function test_mail_config_merges_with_framework_defaults_and_exposes_reply_to(): void
    {
        $this->assertFileExists(config_path('mail.php'));
        $this->assertIsString(config('mail.default'));
        $this->assertIsArray(config('mail.mailers.smtp'));

        $from = config('mail.from');
        $replyTo = config('mail.reply_to');

        $this->assertIsArray($from);
        $this->assertArrayHasKey('address', $from);
        $this->assertArrayHasKey('name', $from);

        $this->assertIsArray($replyTo);
        $this->assertArrayHasKey('address', $replyTo);
        $this->assertArrayHasKey('name', $replyTo);

        $mailConfigSource = file_get_contents(config_path('mail.php'));
        $this->assertIsString($mailConfigSource);
        $this->assertStringContainsString('MAIL_REPLY_TO_ADDRESS', $mailConfigSource);
        $this->assertStringContainsString('MAIL_REPLY_TO_NAME', $mailConfigSource);
    }

    public function test_mailer_applies_configured_from_and_reply_to_and_skips_null_reply_to(): void
    {
        $originalFrom = config('mail.from');
        $originalReplyTo = config('mail.reply_to');

        try {
            Config::set('mail.from', [
                'address' => 'contato@assin.art',
                'name' => 'Assinart',
            ]);
            Config::set('mail.reply_to', [
                'address' => 'sac@assin.art',
                'name' => 'Assinart',
            ]);

            Mail::purge('array');
            $mailer = Mail::mailer('array');
            $mailer->to('artista@assin.art')->send(
                new VerifyEmailMail('Artista Teste', 'artista@assin.art', 'token-teste-123')
            );

            $sentMessages = $mailer->getSymfonyTransport()->messages();
            $this->assertCount(1, $sentMessages);

            $emailWithReplyTo = $sentMessages->first()->getOriginalMessage();
            $this->assertInstanceOf(Email::class, $emailWithReplyTo);
            $this->assertSame('contato@assin.art', $emailWithReplyTo->getFrom()[0]->getAddress());
            $this->assertSame('sac@assin.art', $emailWithReplyTo->getReplyTo()[0]->getAddress());

            Config::set('mail.reply_to', [
                'address' => null,
                'name' => 'Assinart',
            ]);

            Mail::purge('array');
            $mailerWithoutReplyTo = Mail::mailer('array');
            $mailerWithoutReplyTo->to('artista@assin.art')->send(
                new VerifyEmailMail('Artista Teste', 'artista@assin.art', 'token-teste-456')
            );

            $sentWithoutReplyTo = $mailerWithoutReplyTo->getSymfonyTransport()->messages();
            $this->assertCount(1, $sentWithoutReplyTo);

            $emailWithoutReplyTo = $sentWithoutReplyTo->first()->getOriginalMessage();
            $this->assertInstanceOf(Email::class, $emailWithoutReplyTo);
            $this->assertCount(0, $emailWithoutReplyTo->getReplyTo());
        } finally {
            Config::set('mail.from', $originalFrom);
            Config::set('mail.reply_to', $originalReplyTo);
            Mail::purge('array');
        }
    }
}
