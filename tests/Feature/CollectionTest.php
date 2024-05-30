<?php

namespace Tests\Feature;

use App\Data\Person;
use Illuminate\Support\LazyCollection;
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


    // Retrieve (untuk mengambil data di collection)
    // first() --> mengambil data pertama di collection, atau null jika tidak ada
    // firstOfFail() --> mengambil data pertama di collection, atau error ItemNotFoundException jika tidak ada
    // first(function) --> mengambil data pertama di collection yang sesuai dengan kondisi function jika menghasilkan nilai true
    // firstWhere(key, value) --> mengambil data pertama di collection sesuai dengan key dan value yang diberikan
    public function testFirst()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->first();
        $this->assertEquals(1, $result);

        $result = $collection->first(function ($value, $key) {
            return $value > 5;
        });
        $this->assertEquals(6, $result);

    }
    // last() --> mengambil data terakhir di collection, atau null jika tidak ada
    // last(function) --> mengambil data terakhir di collection yang sesuai dengan kondisi function jika menghasilkan nilai true
    public function testLast()
    {

        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->last();
        $this->assertEquals(9, $result);

        $result = $collection->last(function ($value, $key) {
            return $value < 5;
        });
        $this->assertEquals(4, $result);
    }


    // Random (mengambil data di collection secara acak)
    // random() --> mengambil satu data collection dengan posisi random
    // random(total) --> mengambil sejumlah total data di collection dengan posisi random
    public function testRandom()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->random();
        // $result = $collection->random(5);

        $this->assertTrue(in_array($result, [1, 2, 3, 4, 5, 6, 7, 8, 9]));
    }


    // Checking Existeance (mengecek apakah data yang dicari ada atau tidak di collection)
    // isEmpty() --> mengecek apakah collection kosong
    // isNotEmpty() --> mengecek apakah collection tidak kosong
    // contains(value) --> mengecek apakah collection memiliki value yang dicari
    // contains(function) --> mengecek apakah collection memiliki value dengan kondisi function yang menghasilkan true
    // containsOneItem() --> mengecek apakah collection hanya memiliki satu data
    public function testCheckingExistence()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $this->assertTrue($collection->isNotEmpty());
        $this->assertFalse($collection->isEmpty());
        $this->assertTrue($collection->contains(1));
        $this->assertFalse($collection->contains(10));
        $this->assertTrue($collection->contains(function ($value, $key) {
            return $value == 8;
        }));
    }


    // Ordering (mengurutkan data di collection)
    // sort() --> mengurutkan secara ascending
    // sortBy(key/function) --> mengurutkan secara ascending berdasarkan key atau function
    // sortDesc() --> mengurutkan secara descending
    // sortByDesc(key/function) --> mengurutkan secara ascending berdasarkan key atau function
    // sortKeys() --> mengurutkan secara ascending berdasarkan keys
    // sortKeysDesc() --> mengurutkan secara descending berdasarkan keys
    // reverse() --> membalikkan urutan collection
    public function testOrdering()
    {
        $collection = collect([1, 3, 2, 4, 6, 5, 8, 7, 9]);
        $result = $collection->sort();
        $this->assertEqualsCanonicalizing([1, 2, 3, 4, 5, 6, 7, 8, 9], $result->all());

        $result = $collection->sortDesc();
        $this->assertEqualsCanonicalizing([9, 8, 7, 6, 5, 4, 3, 2, 1], $result->all());

    }


    // Aggregate
    // min(), max(), average()/avg(), sum(),
    // count() --> mengambil total seluruh data
    public function testAggregate()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->sum();
        $this->assertEquals(45, $result);

        $result = $collection->avg();
        $this->assertEquals(5, $result);

        $result = $collection->min();
        $this->assertEquals(1, $result);

        $result = $collection->max();
        $this->assertEquals(9, $result);
    }


    // Reduce (operasi yang dilakukan disetiap data yg ada di collection secara sequential, dimana hasil dari reduce sebelumnya akan digunakan diiterasi selanjutnya)
    // reduce(fucntion(carry, item)) --> pada iterasi pertama, carry akan bernilai data pertama, dan item adalah data selanjutnya, pada iterasi selanjutnya, carry adalah hasil dari iterasi sebelumnya, itemm adalah data selanjutnya
    public function testReduce()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->reduce(function ($carry, $item) {
            return $carry + $item;
        });
        $this->assertEquals(45, $result);

        // reduce(1,2) = 3
        // reduce(3,3) = 6
        // reduce(6,4) = 10
        // reduce(10,5) = 15
        // reduce(15,6) = 21
        // reduce(21,7) = 28
    }


    // Lazy Collection (operasi akan dieksekusi hanya ketika dibutuhkan)
    // yield digunakan untuk membuat generator dalam PHP. Generator adalah fungsi khusus yang memungkinkan mengembalikan nilai satu per satu, Setiap kali yield dieksekusi, ia akan "menghentikan" eksekusi fungsi sementara, dan mengembalikan nilai ke caller (pemanggil). Fungsi akan dilanjutkan dari tempat terakhir saat generator dipanggil kembali.
    public function testLazyCollection()
    {

        $collection = LazyCollection::make(function () {
            $value = 0;

            while (true) {
                yield $value;
                $value++;
            }
        });

        $result = $collection->take(10); // ketika dibutuhkan baru akan dibuatkan collection sesuai kebutuhan
        $this->assertEqualsCanonicalizing([0, 1, 2, 3, 4, 5, 6, 7, 8, 9], $result->all());
    }
}
