<?php

namespace tests\unit;

use panix\mod\user\models\User;

class FirstTest extends \PHPUnit_Framework_TestCase
{
    public function testTrue()
    {
        $this->assertTrue(true);
    }


    public function testSaveManyToMany()
    {
        //load
        $book = User::findOne(5);

        //simulate form input
        $post = [
            'User' => [
                'id' => [7, 9, 8]
            ]
        ];

        $this->assertTrue($book->load($post), 'Load POST data');
        $this->assertTrue($book->save(), 'Save model');

        //reload
        $book = User::findOne(5);

        //must have three authors
        $this->assertEquals(3, count($book->authors), 'Author count after save');

        //must have authors 7, 8, and 9
        $author_keys = array_keys($book->getAuthors()->indexBy('id')->all());
        $this->assertContains(7, $author_keys, 'Saved author exists');
        $this->assertContains(8, $author_keys, 'Saved author exists');
        $this->assertContains(9, $author_keys, 'Saved author exists');
    }
}