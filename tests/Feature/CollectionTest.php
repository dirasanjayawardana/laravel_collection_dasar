<?php

namespace Tests\Feature;

use Tests\TestCase;

class CollectionTest extends TestCase
{
    // collection --> sebuah tipe data yang mirip seperti array namun berupa objek yang memiliki banyak method untuk melakukan manipulasi data

    public function testCreateCollection()
    {
        // membuat collection --> collect([array])
        $collection = collect([1, 2, 3]);

        // merubah collection ke array --> $collection->all();
        $this->assertEquals([1, 2, 3], $collection->all()); // membandinkan nilai dan urutannya
        $this->assertEqualsCanonicalizing([1, 2, 3], $collection->all()); // hanya membandingkan nilainya saja
    }

    // karena collection merupakan turunan dari Iterable maka bisa dilakukan iterasi
    public function testForeach()
    {
        $collection = collect([1, 2, 3, 4, 5, 6]);
        foreach ($collection as $key => $value) {
            self::assertEquals($key + 1, $value);
        }
    }

    // Method pada collection
    // push(data) --> menambahkan data ke paling belakang
    // pop() --> menghapus dan mengambil data paling akhir
    // prepend(data) --> menambahkan data ke paling depan
    // pull(key) --> menghapus dan mengambil data sesuai dengan key
    // put(key, data) --> mengubah data sesuai dengan key
    public function testCrud()
    {
        $collection = collect([]);
        $collection->push(1, 2, 3);
        $this->assertEqualsCanonicalizing([1, 2, 3], $collection->all());

        $result = $collection->pop();
        $this->assertEquals(3, $result);
        $this->assertEqualsCanonicalizing([1, 2], $collection->all());
    }
}
