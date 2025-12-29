<?php

namespace tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use app\models\Settings;

class SettingsTest extends TestCase
{
    public function testSetAndGetSetting()
    {
        Settings::set('site_name', 'My Site');

        $value = Settings::get('site_name');

        $this->assertEquals('My Site', $value);
    }

    public function testGetNonExistentSettingReturnsDefault()
    {
        $value = Settings::get('non_existent_key', 'default_value');

        $this->assertEquals('default_value', $value);
    }

    public function testGetNonExistentSettingReturnsNullWithoutDefault()
    {
        $value = Settings::get('non_existent_key');

        $this->assertNull($value);
    }

    public function testUpdateExistingSetting()
    {
        Settings::set('site_name', 'Old Name');
        Settings::set('site_name', 'New Name');

        $value = Settings::get('site_name');

        $this->assertEquals('New Name', $value);
    }

    public function testSetMultipleSettings()
    {
        Settings::set('site_name', 'My Site');
        Settings::set('site_description', 'My Description');
        Settings::set('site_email', 'contact@example.com');

        $this->assertEquals('My Site', Settings::get('site_name'));
        $this->assertEquals('My Description', Settings::get('site_description'));
        $this->assertEquals('contact@example.com', Settings::get('site_email'));
    }

    public function testGetAllSettings()
    {
        Settings::set('site_name', 'My Site');
        Settings::set('site_description', 'My Description');
        Settings::set('site_email', 'contact@example.com');

        $all = Settings::getAll();

        $this->assertIsArray($all);
        $this->assertArrayHasKey('site_name', $all);
        $this->assertArrayHasKey('site_description', $all);
        $this->assertArrayHasKey('site_email', $all);
        $this->assertEquals('My Site', $all['site_name']);
        $this->assertEquals('My Description', $all['site_description']);
        $this->assertEquals('contact@example.com', $all['site_email']);
    }

    public function testGetAllReturnsEmptyArrayWhenNoSettings()
    {
        $all = Settings::getAll();

        $this->assertIsArray($all);
        $this->assertCount(0, $all);
    }

    public function testSettingWithSpecialCharacters()
    {
        $value = 'Test with "quotes" and \'apostrophes\' & symbols <>';
        Settings::set('special_chars', $value);

        $retrieved = Settings::get('special_chars');

        $this->assertEquals($value, $retrieved);
    }

    public function testSettingWithUnicode()
    {
        $value = 'Hello 世界 🌍';
        Settings::set('unicode_test', $value);

        $retrieved = Settings::get('unicode_test');

        $this->assertEquals($value, $retrieved);
    }

    public function testSettingWithLongValue()
    {
        $longValue = str_repeat('a', 10000);
        Settings::set('long_value', $longValue);

        $retrieved = Settings::get('long_value');

        $this->assertEquals($longValue, $retrieved);
    }

    public function testSettingWithEmptyString()
    {
        Settings::set('empty_string', '');

        $retrieved = Settings::get('empty_string');

        $this->assertEquals('', $retrieved);
    }

    public function testSettingWithZero()
    {
        Settings::set('zero_value', 0);

        $retrieved = Settings::get('zero_value');

        $this->assertEquals('0', $retrieved);
    }

    public function testSettingWithBooleanTrue()
    {
        Settings::set('boolean_true', true);

        $retrieved = Settings::get('boolean_true');

        $this->assertEquals('1', $retrieved);
    }

    public function testSettingWithBooleanFalse()
    {
        Settings::set('boolean_false', false);

        $retrieved = Settings::get('boolean_false');

        $this->assertEquals('', $retrieved);
    }

    public function testSettingWithJsonString()
    {
        $json = '{"key": "value", "number": 123}';
        Settings::set('json_setting', $json);

        $retrieved = Settings::get('json_setting');

        $this->assertEquals($json, $retrieved);
    }

    public function testOverwriteSettingWithDifferentType()
    {
        Settings::set('flexible', 'string value');
        $this->assertEquals('string value', Settings::get('flexible'));

        Settings::set('flexible', '123');
        $this->assertEquals('123', Settings::get('flexible'));

        Settings::set('flexible', 'new value');
        $this->assertEquals('new value', Settings::get('flexible'));
    }

    public function testSettingKeyCaseSensitivity()
    {
        Settings::set('CaseSensitive', 'value1');
        Settings::set('casesensitive', 'value2');

        $this->assertEquals('value1', Settings::get('CaseSensitive'));
        $this->assertEquals('value2', Settings::get('casesensitive'));
    }

    public function testGetAllAfterMultipleUpdates()
    {
        Settings::set('key1', 'value1');
        Settings::set('key2', 'value2');
        Settings::set('key3', 'value3');

        Settings::set('key2', 'updated_value2');

        $all = Settings::getAll();

        $this->assertEquals('value1', $all['key1']);
        $this->assertEquals('updated_value2', $all['key2']);
        $this->assertEquals('value3', $all['key3']);
    }
}
