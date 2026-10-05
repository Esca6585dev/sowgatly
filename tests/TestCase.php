<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Contract\RecordsContract;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RecordsContract;
}