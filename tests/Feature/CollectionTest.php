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


    // CRUD pada collection
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


    // Transformation (merubah bentuk data menjadi data baru)
    // map(function) --> iterasi seluruh data, dan mengirim seluruh data ke function
    // mapInto(class) --> iterasi seluruh data, dan membuat object baru untuk setiap class dengan mengirim parameter tiap data
    // mapSpread(function) --> iterasi seluruh data, dan mengirim tiap data sebagai parameter di function
    // mapToGroups(function) --> iterasi seluruh data, dan mengirim tiap data ke function, function harus mengembalikan single key-value array untuk di group sebagai collection baru
    public function testMap()
    {
        $collection = collect([1, 2, 3]);
        $result = $collection->map(function ($value, $key) {
            return $value * 2;
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


    // Zipping (menggabungkan collection)
    // zip(collection/array) --> menggabungkan tiap item di collection sehingga menjadi collection baru
    // concatzip(collection/array) --> menambahkan collection pada bagian akhir sehingga menjadi collection baru
    // combinezip(collection/array) --> menggabungkan collection sehingga collection pertama menjadi key dan collection kedua menjadi value
    public function testZip()
    {
        $collection1 = collect([1, 2, 3]);
        $collection2 = collect([4, 5, 6]);

        // 1 dan 4 akan menjadi collection baru, 2 dan 5 akan menjadi collection baru, 3 dan 6 akan menjadi collection baru, didalam collection3
        $collection3 = $collection1->zip($collection2);

        $this->assertEquals([
            collect([1, 4]),
            collect([2, 5]),
            collect([3, 6]),
        ], $collection3->all());
    }
    public function testConcat()
    {
        $collection1 = collect([1, 2, 3]);
        $collection2 = collect([4, 5, 6]);

        $collection3 = $collection1->concat($collection2);

        $this->assertEquals([1, 2, 3, 4, 5, 6], $collection3->all());
    }
    public function testCombine()
    {
        $collection1 = collect(["name", "country"]);
        $collection2 = collect(["Dira", "Indonesia"]);

        $collection3 = $collection1->combine($collection2);

        $this->assertEquals([
            "name" => "Dira",
            "country" => "Indonesia"
        ], $collection3->all());
    }


    // Flattening (transformasi nested collection menjadi flat atau tidak nested lagi)
    // collapse() --> mengubah tiap array di item collection menjadi flat collection
    // flatMap() --> iterasi tiap data, dikirim ke function yang menghasilkan collection, dan diubah menjadi flat collection
    public function testCollapse()
    {
        $collection = collect([
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9]
        ]);
        $result = $collection->collapse();

        $this->assertEquals([1, 2, 3, 4, 5, 6, 7, 8, 9], $result->all());
    }
    public function testFlatMap()
    {
        $collection = collect([
            [
                "name" => "Dira",
                "hobbies" => ["Coding", "Gaming"],
            ],
            [
                "name" => "Sanjaya",
                "hobbies" => ["Reading", "Writing"],
            ],
        ]);

        $hobbies = $collection->flatMap(function ($item) {
            return $item["hobbies"];
        });

        $this->assertEquals(["Coding", "Gaming", "Reading", "Writing"], $hobbies->all());
    }


    // String Representation (merubah collection menjadi string)
    // join(glue: '', finalGlue: '') --> mengubah tiap item manjadi string dengan menggabungkan dengan separator glue, dan separator akhir finalGlue
    public function testJoin()
    {
        $collection = collect(["Dira", "Sanjaya", "Wardana"]);

        $this->assertEquals("Dira-Sanjaya-Wardana", $collection->join("-"));
        $this->assertEquals("Dira-Sanjaya=Wardana", $collection->join("-", "="));
        $this->assertEquals("Dira, Sanjaya and Wardana", $collection->join(", ", " and "));
    }


    // Filtering (melakukan filter pada collection menjadi collection baru)
    // filter(function) --> iterasi setiap data, dikirim ke function, jika true maka data diambil, jika false maka data dibuang
    public function testFilter()
    {
        $collection = collect([
            "Dira" => 100,
            "Sanjaya" => 90,
            "Wardana" => 80
        ]);

        $result = $collection->filter(function ($value, $key) {
            return $value >= 90;
        });

        $this->assertEquals([
            "Dira" => 100,
            "Sanjaya" => 90
        ], $result->all());
    }
    // hati-hati jika collection tidak memiliki key, atau key hanya berupa index, maka jika false indexnya juga dibuang
    public function testFilterIndex()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

        $result = $collection->filter(function ($value, $key) {
            return $value % 2 == 0;
        });

        $this->assertEquals([
            1 => 2,
            3 => 4,
            5 => 6,
            7 => 8,
            9 => 10,
        ], $result->all());
    }


    // Partitioning (mirip dengan filter, namun akan menghasilkan dua collection, satu collection kumpulan yg true, dan satu collection kumpulan yg false)
    // partition(function) --> iterasi setiap data, dikirim ke function, jika true maka data masuk ke collection pertama, jika false maka data masuk ke collection kedua
    public function testPartition()
    {
        $collection = collect([
            "Dira" => 100,
            "Sanjaya" => 90,
            "Wardana" => 80
        ]);

        [$result1, $result2] = $collection->partition(function ($value, $key) {
            return $value >= 90;
        });

        $this->assertEquals(["Dira" => 100, "Sanjaya" => 90], $result1->all());
        $this->assertEquals(["Wardana" => 80], $result2->all());
    }


    // Testing (operasi untuk mengecek isi data di collection, balikannya adalah boolean)
    // has(array) --> mengecek apakah collection memiliki semua key yg ada di array
    // hasAny(array) --> mengecek apakah collection memilihi salah satu key yg ada di array
    // contains(value) --> mengecek apakah collection memiliki data value
    // contains(key, value) --> mengecek apakah collection memiliki data key dengan value
    // contains(function) --> iterasi tiap data, mengirim ke function dan mengecek apakah salah satu data menghasilkan true
    public function testTesting()
    {
        $collection = collect(["Dira", "Sanjaya", "Wardana"]);
        self::assertTrue($collection->contains("Dira"));
        self::assertTrue($collection->contains(function ($value, $key) {
            return $value == "Dira";
        }));
    }


    // Grouping (menggabungkan element-element yang ada di collection)
    // groupBy(key) --> menggabungkan data collection per key
    // groupBy(function) --> menggabungkan data collection per hasil function
    public function testGrouping()
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
        $result = $collection->groupBy("department");
        $this->assertEquals([
            "IT" => collect([
                [
                    "name" => "Dira",
                    "department" => "IT"
                ],
                [
                    "name" => "Sanjaya",
                    "department" => "IT"
                ]
            ]),
            "HR" => collect([
                [
                    "name" => "Wardana",
                    "department" => "HR"
                ]
            ])
        ], $result->all());

        $result2 = $collection->groupBy(function ($value, $key) {
            return $value["department"];
        });
        $this->assertEquals([
            "IT" => collect([
                [
                    "name" => "Dira",
                    "department" => "IT"
                ],
                [
                    "name" => "Sanjaya",
                    "department" => "IT"
                ]
            ]),
            "HR" => collect([
                [
                    "name" => "Wardana",
                    "department" => "HR"
                ]
            ])
        ], $result2->all());
    }


    // Slicing (operasi untuk mengambil sebagian data di collection)
    // slice(startIndex) --> mengambil data mulai dari start sampai data terakhir
    // slice(startIndex, length) --> mengambil data mulai dari start sepanjang length
    public function testSlice()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->slice(3);
        $this->assertEqualsCanonicalizing([4, 5, 6, 7, 8, 9], $result->all());

        $result2 = $collection->slice(3, 2);
        $this->assertEqualsCanonicalizing([4, 5], $result2->all());
    }


    // Take & Skip (untuk mengambil sebagian data di collection)
    // take(length) --> mengambil data dari awal sepanjang length, jika length negative artinya proses pengambilan dari posisi belakang
    // takeUntil(function) --> iterasi tiap data, ambil tiap data sampai function mengembalikan nilai true, kemudian iterasi dihentikan
    // takeWhile(function) --> iterasi tiap data, ambil tiap data sampai function mengembalikan nilai false, kemudian iterasi dihentikan
    public function testTake()
    {
        $collection = collect([1, 2, 3, 1, 2, 3, 1, 2, 3]);

        $result = $collection->take(3);
        $this->assertEqualsCanonicalizing([1, 2, 3], $result->all());

        $result = $collection->takeUntil(function ($value, $key) {
            return $value == 3;
        });
        $this->assertEqualsCanonicalizing([1, 2], $result->all());

        $result = $collection->takeWhile(function ($value, $key) {
            return $value < 3;
        });
        $this->assertEqualsCanonicalizing([1, 2], $result->all());
    }
    // skip(length) --> ambil seluruh data kecuali sejumlah length diawal
    // skipUntil(function) --> iterasi tiap data, skip sampai ...
    // skipWhile(function) --> iterasi tiap data, skip saat ...
    public function testSkip()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);

        $result = $collection->skip(3);
        $this->assertEqualsCanonicalizing([4, 5, 6, 7, 8, 9], $result->all());

        $result = $collection->skipUntil(function ($value, $key) {
            return $value == 3;
        });
        $this->assertEqualsCanonicalizing([3, 4, 5, 6, 7, 8, 9], $result->all());

        $result = $collection->skipWhile(function ($value, $key) {
            return $value < 3;
        });
        $this->assertEqualsCanonicalizing([3, 4, 5, 6, 7, 8, 9], $result->all());
    }


    // Chunked (untuk memotong collection menjadi beberapa collection)
    // chunk(number) --> potong collection menjadi lebih kecil dimana tiap collection memiliki sejumlah total data number
    public function testChunk()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

        $result = $collection->chunk(3);

        $this->assertEqualsCanonicalizing([1, 2, 3], $result->all()[0]->all());
        $this->assertEqualsCanonicalizing([4, 5, 6], $result->all()[1]->all());
        $this->assertEqualsCanonicalizing([7, 8, 9], $result->all()[2]->all());
        $this->assertEqualsCanonicalizing([10], $result->all()[3]->all());
    }
}
