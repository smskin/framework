<?php

namespace Illuminate\Tests\Integration\Database\MySql;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * @requires extension pdo_mysql
 * @requires OS Linux|Darwin
 */
class MatchAgainstTest extends MySqlTestCase
{
    protected function defineDatabaseMigrationsAfterDatabaseRefreshed()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id('id');
            $table->string('title', 200);
            $table->text('body');
            $table->fulltext(['title', 'body']);
        });
    }

    protected function destroyDatabaseMigrations()
    {
        Schema::drop('articles');
    }

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('articles')->insert([
            ['title' => 'MySQL Tutorial', 'body' => 'DBMS stands for DataBase ...'],
            ['title' => 'How To Use MySQL Well', 'body' => 'After you went through a ...'],
            ['title' => 'Optimizing MySQL', 'body' => 'In this tutorial, we show ...'],
            ['title' => '1001 MySQL Tricks', 'body' => '1. Never run mysqld as root. 2. ...'],
            ['title' => 'MySQL vs. YourSQL', 'body' => 'In the following database comparison ...'],
            ['title' => 'MySQL Security', 'body' => 'When configured properly, MySQL ...'],
        ]);
    }

    /** @link https://dev.mysql.com/doc/refman/8.0/en/fulltext-natural-language.html */
    public function testMatchAgainst()
    {
        $articles = DB::table('articles')->matchAgainst(['title', 'body'], 'database')->get();

        $this->assertCount(2, $articles);
        $this->assertSame('MySQL Tutorial', $articles[0]->title);
        $this->assertSame('MySQL vs. YourSQL', $articles[1]->title);
    }

    /** @link https://dev.mysql.com/doc/refman/8.0/en/fulltext-boolean.html */
    public function testMatchAgainstBoolean()
    {
        $articles = DB::table('articles')->matchAgainstBoolean(['title', 'body'], '+MySQL -YourSQL')->get();

        $this->assertCount(5, $articles);
    }

    /** @link https://dev.mysql.com/doc/refman/8.0/en/fulltext-query-expansion.html */
    public function testMatchAgainstExpanded()
    {
        $articles = DB::table('articles')->matchAgainstExpanded(['title', 'body'], 'database')->get();

        $this->assertCount(6, $articles);
    }
}
