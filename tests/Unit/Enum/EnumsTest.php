<?php

namespace Kontur\Talk\Tests\Unit\Enum;

use Kontur\Talk\Enum\LinkAccessScope;
use Kontur\Talk\Enum\ScopeRestrictionType;
use Kontur\Talk\Enum\ScopeType;
use Kontur\Talk\Enum\SpeechCoreResultStatus;
use Kontur\Talk\Enum\SummaryType;
use Kontur\Talk\Enum\TranscriptionStatus;
use Kontur\Talk\Enum\WebhookEventType;
use PHPUnit\Framework\TestCase;

/**
 * Значения enum'ов должны дословно совпадать со спецификацией OpenAPI — тест фиксирует их
 * как регресс-защиту от опечаток.
 */
class EnumsTest extends TestCase
{
    public function testSummaryTypeValues(): void
    {
        $this->assertSame(
            ['shortSummary', 'protocol'],
            array_column(SummaryType::cases(), 'value')
        );
    }

    public function testLinkAccessScopeValues(): void
    {
        $this->assertSame(
            ['none', 'domain', 'global'],
            array_column(LinkAccessScope::cases(), 'value')
        );
    }

    public function testTranscriptionStatusValues(): void
    {
        $this->assertSame(
            ['inProgress', 'error', 'complete'],
            array_column(TranscriptionStatus::cases(), 'value')
        );
    }

    public function testSpeechCoreResultStatusValues(): void
    {
        $this->assertSame(
            [
                'notFound',
                'inProgress',
                'failed',
                'success',
                'notAvailable',
                'serviceError',
                'recreateInProgress',
            ],
            array_column(SpeechCoreResultStatus::cases(), 'value')
        );
    }

    public function testScopeTypeValues(): void
    {
        $this->assertSame(
            [
                'profiles',
                'calendar',
                'calendarControl',
                'rooms',
                'reporting',
                'kiosk',
                'recording',
                'routing',
                'onlineStats',
                'applications',
                'roles',
                'corpTelephony',
                'spectatorRegistration',
                'federations',
                'redirect',
                'streamEvents',
                'deepfakeDetection',
                'surveys',
                'webhooks',
                'messengerStats',
                'messengerLicense',
                'activeRecordings',
            ],
            array_column(ScopeType::cases(), 'value')
        );
    }

    public function testScopeRestrictionTypeValues(): void
    {
        $this->assertSame(
            ['read', 'readWrite'],
            array_column(ScopeRestrictionType::cases(), 'value')
        );
    }

    public function testWebhookEventTypeValues(): void
    {
        $this->assertSame(
            [
                'recordingCompleted',
                'transcriptionReady',
                'userConnectedToRoom',
                'userDisconnectedFromRoom',
                'conferencesStarted',
                'conferencesFinished',
            ],
            array_column(WebhookEventType::cases(), 'value')
        );
    }

    public function testFromRejectsUnknownValue(): void
    {
        $this->expectException(\ValueError::class);

        SummaryType::from('unknown');
    }
}
