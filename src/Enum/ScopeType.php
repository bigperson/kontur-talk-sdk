<?php

namespace Kontur\Talk\Enum;

/**
 * Область API-ключа (поле `type` в ответе `GET /api/domain/applications/access-info`).
 *
 * Не используется методами SDK для валидации входных значений — `Applications::accessInfo()`
 * не принимает параметров, это перечисление только для чтения значений из ответа.
 */
enum ScopeType: string
{
    case Profiles = 'profiles';
    case Calendar = 'calendar';
    case CalendarControl = 'calendarControl';
    case Rooms = 'rooms';
    case Reporting = 'reporting';
    case Kiosk = 'kiosk';
    case Recording = 'recording';
    case Routing = 'routing';
    case OnlineStats = 'onlineStats';
    case Applications = 'applications';
    case Roles = 'roles';
    case CorpTelephony = 'corpTelephony';
    case SpectatorRegistration = 'spectatorRegistration';
    case Federations = 'federations';
    case Redirect = 'redirect';
    case StreamEvents = 'streamEvents';
    case DeepfakeDetection = 'deepfakeDetection';
    case Surveys = 'surveys';
    case Webhooks = 'webhooks';
    case MessengerStats = 'messengerStats';
    case MessengerLicense = 'messengerLicense';
    case ActiveRecordings = 'activeRecordings';
}
