<?php

namespace Kontur\Talk\Tests\Unit\Api;

use GuzzleHttp\Psr7\Response;
use Kontur\Talk\Api\Calendar;

class CalendarTest extends ApiTestCase
{
    private function sampleEvent(): array
    {
        // Форма ответа — EmailCalendarItem из OpenAPI-спеки (используемое подмножество полей)
        return [
            'id' => 'event-1',
            'onlineMeetingUrl' => 'https://testspace.ktalk.ru/some-room',
            'roomName' => 'sales-room',
            'start' => '2026-09-01T10:00:00Z',
            'end' => '2026-09-01T11:00:00Z',
            'subject' => 'Демо для клиента',
            'organizer' => ['mailbox' => 'manager@example.com', 'name' => 'Менеджер'],
            'requiredAttendees' => [],
            'enableAutoRecording' => true,
            'isCancelled' => false,
        ];
    }

    public function testCreateEventSendsPostWithBodyVerbatimIncludingApiTypo(): void
    {
        $data = [
            'start' => '2026-09-01T10:00:00Z',
            'end' => '2026-09-01T11:00:00Z',
            'subject' => 'Демо для клиента',
            'roomName' => 'sales-room',
            'timezone' => 'Europe/Moscow',
            'description' => 'Обсуждение условий',
            'allowAnonymous' => true,
            'pinCode' => '1234',
            'enableAutoRecording' => true,
            'requiredUserKeys' => ['user-1'],
            'optionalUserKeys' => ['user-2'],
            'requiredExternalAttendeesEmails' => ['client@example.com'],
            // Опечатка "optionExternal..." — так объявлено в самой спецификации API, сохраняем её
            'optionExternalAttendeesEmails' => ['guest@example.com'],
        ];

        $response = $this->sampleEvent();

        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode($response))], $history);
        $calendar = new Calendar($client);

        $result = $calendar->createEvent('manager@example.com', $data);

        $this->assertEquals($response, $result);

        $request = $this->lastRequest($history);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/EmailCalendar/manager@example.com', $request->getUri()->getPath());
        $this->assertSame($data, $this->jsonBody($request));
        $this->assertArrayHasKey('optionExternalAttendeesEmails', $this->jsonBody($request));
    }

    public function testListEventsRequiresStartAndAcceptsOptionalFilters(): void
    {
        $response = ['items' => [$this->sampleEvent()]];

        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode($response))], $history);
        $calendar = new Calendar($client);

        $result = $calendar->listEvents(
            'manager@example.com',
            '2026-09-01T00:00:00Z',
            '2026-09-30T00:00:00Z',
            100
        );

        $this->assertEquals($response, $result);

        $request = $this->lastRequest($history);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/EmailCalendar/manager@example.com', $request->getUri()->getPath());
        $this->assertSame([
            'start' => '2026-09-01T00:00:00Z',
            'end' => '2026-09-30T00:00:00Z',
            'take' => '100',
        ], $this->queryParams($request));
    }

    public function testListEventsWithOnlyStartOmitsOptionalParams(): void
    {
        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode(['items' => []]))], $history);
        $calendar = new Calendar($client);

        $calendar->listEvents('manager@example.com', '2026-09-01T00:00:00Z');

        $request = $this->lastRequest($history);
        $this->assertSame(['start' => '2026-09-01T00:00:00Z'], $this->queryParams($request));
    }

    public function testUpdateEventSendsPutWithBody(): void
    {
        $data = [
            'start' => '2026-09-01T10:00:00Z',
            'end' => '2026-09-01T11:30:00Z',
            'subject' => 'Демо для клиента (перенесено)',
            'roomName' => 'sales-room',
        ];
        $response = array_merge($this->sampleEvent(), $data);

        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode($response))], $history);
        $calendar = new Calendar($client);

        $result = $calendar->updateEvent('manager@example.com', 'event-1', $data);

        $this->assertEquals($response, $result);

        $request = $this->lastRequest($history);
        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame('/api/EmailCalendar/manager@example.com/event-1', $request->getUri()->getPath());
        $this->assertSame($data, $this->jsonBody($request));
    }

    public function testDeleteEventSendsDeleteWithoutMessage(): void
    {
        $history = [];
        $client = $this->mockClient([new Response(200)], $history);
        $calendar = new Calendar($client);

        $calendar->deleteEvent('manager@example.com', 'event-1');

        $request = $this->lastRequest($history);
        $this->assertSame('DELETE', $request->getMethod());
        $this->assertSame('/api/EmailCalendar/manager@example.com/event-1', $request->getUri()->getPath());
        $this->assertSame('', $request->getUri()->getQuery());
    }

    public function testDeleteEventSendsMessageAsQueryParam(): void
    {
        $history = [];
        $client = $this->mockClient([new Response(200)], $history);
        $calendar = new Calendar($client);

        $calendar->deleteEvent('manager@example.com', 'event-1', 'Встреча отменена');

        $request = $this->lastRequest($history);
        $this->assertSame(['message' => 'Встреча отменена'], $this->queryParams($request));
    }

    public function testUpdateAttendeesSendsPostWithBodyIncludingApiTypo(): void
    {
        $data = [
            'requiredUserKeys' => ['user-1'],
            'optionalUserKeys' => [],
            'requiredExternalAttendeesEmails' => [],
            'optionExternalAttendeesEmails' => ['guest@example.com'],
        ];
        $response = $this->sampleEvent();

        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode($response))], $history);
        $calendar = new Calendar($client);

        $result = $calendar->updateAttendees('manager@example.com', 'event-1', $data);

        $this->assertEquals($response, $result);

        $request = $this->lastRequest($history);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame(
            '/api/EmailCalendar/manager@example.com/event-1/attendees',
            $request->getUri()->getPath()
        );
        $this->assertSame($data, $this->jsonBody($request));
    }
}
