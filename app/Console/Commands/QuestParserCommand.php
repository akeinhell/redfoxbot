<?php

namespace App\Console\Commands;

use App\Console\Commands\Parsers\DzzzrParser;
use App\Console\Commands\Parsers\Types\QuestData;
use App\Quest;
use Carbon\Carbon;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Illuminate\Console\Command;


class QuestParserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quest:parse';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсит все квесты';
    private $parsers = [
        'dozor' => DzzzrParser::class,
    ];
    private $calendar;

    private $calendarId = 'c7v4er5p5ciemje60n559jugfk@group.calendar.google.com';

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {

        parent::__construct();
    }

    function getClient()
    {
        $client = new Google_Client();
        $client->setApplicationName(APPLICATION_NAME);
        $client->setScopes(SCOPES);
        $client->setAuthConfigFile(CLIENT_SECRET_PATH);
        $client->setAccessType('offline');

        // Load previously authorized credentials from a file.
        $credentialsPath = CREDENTIALS_PATH;
        if (file_exists($credentialsPath)) {
            $accessToken = file_get_contents($credentialsPath);
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            $accessToken = $client->authenticate($authCode);

            // Store the credentials to disk.
            if (!file_exists(dirname($credentialsPath))) {
                mkdir(dirname($credentialsPath), 0700, true);
            }
            file_put_contents($credentialsPath, $accessToken);
            printf("Credentials saved to %s\n", $credentialsPath);
        }
        $client->setAccessToken($accessToken);

        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {
            $client->refreshToken($client->getRefreshToken());
            file_put_contents($credentialsPath, $client->getAccessToken());
        }

        return $client;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        define('SCOPES', implode(' ', [
                Google_Service_Calendar::CALENDAR,
            ]
        ));
        define('APPLICATION_NAME', 'Google Calendar API PHP Quickstart');
        define('CREDENTIALS_PATH', storage_path() . '/credentials-calendar.json');
        define('CLIENT_SECRET_PATH', storage_path() . '/client_secret.json');
        $client         = $this->getClient();
        $this->calendar = new Google_Service_Calendar($client);
        
//        return $this->calend();
        foreach ($this->parsers as $parserClass) {
            try {
                $quests = $parserClass::getInstance()->init()->startParse();
                /**
                 * @var  $quest
                 */
                foreach ($quests as $quest) {
                    $this->execQuest($quest);
                }
            } catch (\Exception $e) {
                $this->error('Error parse ' . $parserClass . PHP_EOL);
                $this->error(get_class($e) . ' : ' . $e->getMessage());
                debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            }
        }
    }

    /**
     * https://developers.google.com/google-apps/calendar/quickstart/php#step_3_set_up_the_sample
     * @param QuestData $questData
     */
    private function execQuest($questData)
    {
        $this->info('work ' . $questData->getKey());
        $quest = Quest::whereKey($questData->getKey())->first();

        if ($quest) {
            $this->line('update ' . $questData->getTitle());
            $quest->update($questData());
            $quest->save();

            return;
        }

        $this->line('create ' . $questData->getTitle());
        list($id, $link) = $this->createEvent(
            $questData->getTitle(),
            $questData->getDescription(),
            $questData->getStart(),
            $questData->getStop(),
            $questData->getPlacement()
        );
        $questData->setHtmlLink($link);
        $questData->setEventId($id);

        $quest = new Quest($questData());
        $quest->save();
    }

    /**
     * @param string $title
     * @param string $description
     * @param Carbon $start
     * @param Carbon $stop
     * @param string $location
     * @return array
     */
    private function createEvent($title, $description, $start, $stop, $location)
    {
        $params = [
            'summary'     => $title,
            'location'    => $location,
            'description' => $description,
            'start'       => [
                'dateTime' => $start->toAtomString(),
            ],
            'end'         => [
                'dateTime' => $stop->toAtomString(),
            ],
            'reminders'   => [
                'useDefault' => false,
                'overrides'  => [
                    ['method' => 'popup', 'minutes' => 60],
                    ['method' => 'popup', 'minutes' => 60 * 12],
                    ['method' => 'popup', 'minutes' => 60 * 24],
                ],
            ],
        ];
        $event  = new Google_Service_Calendar_Event($params);
        $event  = $this->calendar->events->insert($this->calendarId, $event);

        return [$event->id, $event->htmlLink];

    }
}
