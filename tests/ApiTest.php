<?php


class ApiTest extends TestCase
{
    public function testBasicExample()
    {
        $this
            ->get('api/en/games/demo.en.cx')
            ->seeJsonStructure([
                '*' => ['id', 'type', 'domain', 'start', 'title'],
            ]);
    }

}