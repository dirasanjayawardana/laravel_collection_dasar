<?php

namespace Tests\Feature;

use App\Data\Person;
use Tests\TestCase;

// collection --> sebuah tipe data yang mirip seperti array namun berupa objek yang memiliki banyak method untuk melakukan manipulasi data
class CollectionTest extends TestCase
{
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


    // map(function) --> iterasi seluruh data, dan mengirim seluruh data ke function
    // mapInto(class) --> iterasi seluruh data, dan membuat object baru untuk setiap class dengan mengirim parameter tiap data
    // mapSpread(function) --> iterasi seluruh data, dan mengirim tiap data sebagai parameter di function
    // mapToGroups(function) --> iterasi seluruh data, dan mengirim tiap data ke function, function harus mengembalikan single key-value array untuk di group sebagai collection baru
    public function testMap()
    {
        $collection = collect([1, 2, 3]);
        $result = $collection->map(function ($item) {
            return $item * 2;
        });
        $this->assertEquals([2, 4, 6], $result->all());
    }
    public function testMapInto()
    {
        $collection = collect(["Dira"]);
        $result = $collection->mapInto(Person::class);
        $this->assertEquals([new Person("Dira")], $result->all());
    }
    public function testMapSpread()
    {
        $collection = collect([["Dira", "Sanjaya"], ["Wardana", "Pratama"]]);
        $result = $collection->mapSpread(function ($firstName, $lastName) {
            $fullName = $firstName . " " . $lastName;
            return new Person($fullName);
        });
        $this->assertEquals([
            new Person("Dira Sanjaya"),
            new Person("Wardana Pratama")
        ], $result->all());
    }
    public function testMapToGroups()
    {
        $collection = collect([
            [
                "name" => "Dira",
                "department" => "IT"
            ],
            [
                "name" => "Sanjaya",
                "department" => "IT"
            ],
            [
                "name" => "Wardana",
                "department" => "HR"
            ],
        ]);
        $result = $collection->mapToGroups(function ($item) {
            return [$item["department"] => $item["name"]];
        });
        $this->assertEquals([
            "IT" => collect(["Dira", "Sanjaya"]),
            "HR" => collect(["Wardana"])
        ], $result->all());
    }
}
