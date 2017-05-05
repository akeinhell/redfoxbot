<?php


class HelperTest extends TestCase
{
    public function testCoordinates()
    {
        $goodCoords = [
            '12.345678 12.345678' => [12.345678, 12.345678],
            '12 34 56 78 12 34 56 78' => [12.58243889, 12.582438898],
            '12°34\'56.78"N 12°34\'56.78"E' => [12.58243889, 12.58243889]
        ];
        foreach ($goodCoords as $text => $actual){
            $data = getCoordinates($text);
            $this->assertInternalType('array',$data);
            $this->assertEquals(count($data), 2);
            $this->assertTrue(is_float($data[0]));
            $this->assertTrue(is_float($data[1]));
            foreach ($data as $i => $check){
                $this->assertEquals(round($data[$i], 4), round($actual[$i], 4));
            }
        }
        $data = getCoordinates('3 4');
    }

}