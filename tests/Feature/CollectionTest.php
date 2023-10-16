<?php

namespace Tests\Feature;

use App\Data\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Tests\TestCase;

use function PHPUnit\Framework\assertEquals;

class CollectionTest extends TestCase
{
    public function testCreateCollection()
    {
        $collection = collect([1,2,3]);
        $this->assertEqualsCanonicalizing([1,2,3], $collection->all());
    }

    public function testForEach()
    {
        $collection = collect([1,2,3,4,5,6,7,8,9]);
        foreach($collection as $key => $value)
        {
            self::assertEquals($key + 1, $value);
        }
    }

    public function testCrud()
    {
        $collection = collect([]);
        $collection->push(1,2,3);
        self::assertEqualsCanonicalizing([1,2,3], $collection->all());

        $result = $collection->pop();
        self::assertEquals(3, $result);
        self::assertEqualsCanonicalizing([1,2], $collection->all());

        $result = $collection->push(3);
        self::assertEqualsCanonicalizing([1,2,3], $collection->all());

        $result = $collection->prepend(0);
        self::assertEqualsCanonicalizing([0,1,2,3], $collection->all());

        // put(key, data)
    }

    public function testMap()
    {
        $collection = collect([1,2,3]);
        $result = $collection->map(function(int $item){
            return $item * 2;
        });
        $this->assertEquals([2,4,6], $result->all());
    }

    public function testTransform()
    {
        // testing map (beda instance)
        $collection1 = collect([1,2,3]);
        $result1 = $collection1->map(function(int $item){
            return $item * 2;
        });
        self::assertNotSame($result1, $collection1);

        // testing map (instance sama)
        $collection2 = collect([1,2,3]);
        $result2 = $collection2->transform(function(int $item){
            return $item * 2;
        });
        self::assertSame($result2, $collection2);
    }

    public function testMapInto()
    {
        $collection = collect(['Susi','Fajar','Fahri']);
        $result = $collection->mapInto(Person::class);
        self::assertEquals([new Person('Susi'), new Person('Fajar'), new Person('Fahri')], $result->all());
        self::assertEquals('Hello Susi', $result->all()[0]->sayHello());
        self::assertEquals('Hello Fajar', $result->all()[1]->sayHello());
        self::assertEquals('Hello Fahri', $result->all()[2]->sayHello());
    }

    public function testMapSpread()
    {
        $collection = collect([["Fauzan", "Nurhidayat"],["Andi", "Hermawan"]]);
        $result = $collection->mapSpread(function($firstname, $lastname){
            $fullname = $firstname . " " . $lastname;
            return new Person($fullname);
        });
        self::assertEquals([
            new Person("Fauzan Nurhidayat"),
            new Person("Andi Hermawan")
        ], $result->all());
    }

    public function testMapToGroups()
    {
        $collection = collect([
            [
                'name' => 'Fauzan',
                'department' => 'IT'
            ],
            [
                'name' => 'Susi',
                'department' => 'HR'
            ],
            [
                'name' => 'Rendy',
                'department' => 'HR'
            ],
            [
                'name' => 'Andi',
                'department' => 'IT'
            ],
            ]);
        $result = $collection->mapToGroups(function($item){
            return [$item['department'] => $item['name']];
        });

        self::assertEquals([
            'IT' => collect(['Fauzan', 'Andi']),
            'HR' => collect(['Susi', 'Rendy'])
        ], $result->all());
    }

    public function testZip()
    {
        $collection1 = collect([1,2,3,7]);
        $collection2 = collect([4,5,6]);
        $result = $collection1->zip($collection2);
        self::assertEquals([
            collect([1,4]),
            collect([2,5]),
            collect([3,6]),
            collect([7, null])
        ], $result->all());
    }

    public function testConcat()
    {
        $collection1 = collect([1,2,3]);
        $collection2 = collect([4,5,6]);
        $result = $collection1->concat($collection2);
        self::assertEquals([1,2,3,4,5,6], $result->all());
    }

    public function testCombine()
    {
        $collection1 = collect(['name', 'country']);
        $collection2 = collect(['fauzan', 'indonesia']);
        $result = $collection1->combine($collection2);
        self::assertEquals([
            'name' => 'fauzan',
            'country' => 'indonesia'
        ], $result->all());
    }

    public function testCollapse()
    {
        $collection = collect([
            [1,2,3],
            [4,5,6],
            [7,8,9],
        ]);
        $result = $collection->collapse();
        self::assertEquals([1,2,3,4,5,6,7,8,9], $result->all());
        self::assertEqualsCanonicalizing([9,2,4,1,3,5,6,7,8], $result->all());
    }

    public function testFlatMap()
    {
        $collection = collect([
            [
                'name' => 'fauzan',
                'hobby' => ['coding', 'watching']
            ],
            [
                'name' => 'susi',
                'hobby' => ['reading', 'hiking']
            ]
            ]);
        $result = $collection->flatMap(function($item){
            return $item['hobby'];
        });
        self::assertEquals(['coding', 'watching', 'reading', 'hiking'], $result->all());
    }

    public function testJoin()
    {
        $collection = collect(['apple', 'samsung', 'xiaomi', 'advan', 'vivo']);
        self::assertEquals("apple-samsung-xiaomi-advan_vivo", $collection->join('-', '_'));
        self::assertEquals("apple, samsung, xiaomi, advan and vivo", $collection->join(', ', ' and '));
    }

    public function testFilter()
    {
        $collection = collect([
            'fauzan' => 100,
            'rudi' => 99,
            'joko' => 60,
            'firman' => 30
        ]);
        $result = $collection->filter(function($nilai){
            return $nilai > 50;
        });
        self::assertEquals([
            'fauzan' => 100,
            'rudi' => 99,
            'joko' => 60
        ], $result->all());
    }

    public function testFilterIndex()
    {
        $collection = collect([1,2,3,4,5,6,7,8,9,10]);
        $result = $collection->filter(function($item){
            return $item % 2 == 0;
        });
        // self::assertEquals([2,4,6,8,10], $result->all());
        // menggunakan collection filter pada sebuah collection dengan indeks angka, maka akan menghapus nomor indeksnya
        // jika ingin menghiraukan urutan indeksnya, gunakan assertEqualsCanonicalizing()
        self::assertEqualsCanonicalizing([2,4,6,8,10], $result->all());
    
    }

    public function testPartition()
    {
        $collection = collect([1,2,3,4,5,6,7,8,9,10]);
        $result = $collection->partition(function($value){
            return $value % 2 == 0;
        });
        self::assertEqualsCanonicalizing([
            collect([2,4,6,8,10]),
            collect([1,3,5,7,9])
        ], $result->all());
    }

    public function testContains()
    {
        $collection = collect(['Indonesia', 'Amerika', 'Afrika', 'Arab']);
        self::assertTrue($collection->contains('Indonesia'));
        self::assertTrue($collection->contains('Amerika'));
        self::assertTrue($collection->contains('Afrika'));
        self::assertTrue($collection->contains(function($value){
            return $value = 'Arab';
        }));
    }

    public function testGrouping()
    {
        $collection = collect([
            [
                'name' => 'Fauzan',
                'department' => 'IT'
            ],
            [
                'name' => 'Agus',
                'department' => 'IT'
            ],
            [
                'name' => 'Susi',
                'department' => 'HR'
            ],
            [
                'name' => 'Eko',
                'department' => 'HR'
            ]
            ]);
        $result = $collection->groupBy('department');
        self::assertEquals([
            'IT' => collect([
                [
                    'name' => 'Fauzan',
                    'department' => 'IT'
                ],
                [
                    'name' => 'Agus',
                    'department' => 'IT'
                ]
                
            ]),
            'HR' => collect([
                [
                    'name' => 'Susi',
                    'department' => 'HR'
                ],
                [
                    'name' => 'Eko',
                    'department' => 'HR'
                ],   
            ])
            ], $result->all());
        self::assertEquals([
            'IT' => collect([
                [
                    'name' => 'Fauzan',
                    'department' => 'IT'
                ],
                [
                    'name' => 'Agus',
                    'department' => 'IT'
                ]
                
            ]),
            'HR' => collect([
                [
                    'name' => 'Susi',
                    'department' => 'HR'
                ],
                [
                    'name' => 'Eko',
                    'department' => 'HR'
                ],   
            ])
            ], $collection->groupBy(function($value){
                return $value['department'];
            })->all());
        
    }

    public function testSlice()
    {
        $collection = collect([1,2,3,4,5,6,7,8,9,10]);
        $result1 = $collection->slice(0, 5);
        $result2 = $collection->slice(4);
        self::assertEquals([1,2,3,4,5], $result1->all());
        self::assertEqualsCanonicalizing([5,6,7,8,9,10], $result2->all());
    }

    public function testTake()
    {
        $collection = collect([1,2,3,4,5,6,7,8,9,10]);
        $result = $collection->take(4);
        self::assertEqualsCanonicalizing([1,2,3,4], $result->all());

        $result = $collection->takeUntil(function($value){
            return $value == 3;
        });
        self::assertEquals([1,2], $result->all());

        $result = $collection->takeWhile(function($value){
            return $value < 5;
        });
        self::assertEquals([1,2,3,4], $result->all());
    }

    public function testSkip()
    {
        $collection = collect([1,2,3,4,5,6,7,8,9,10]);
        $result = $collection->skip(4);
        self::assertEqualsCanonicalizing([5,6,7,8,9,10], $result->all());

        $result = $collection->skipUntil(function($value){
            return $value == 3;
        });
        self::assertEqualsCanonicalizing([3,4,5,6,7,8,9,10], $result->all());

        $result = $collection->skipWhile(function($value){
            return $value < 5;
        });
        self::assertEqualsCanonicalizing([5,6,7,8,9,10], $result->all());
    }

    public function testChunked()
    {
        $collection = collect([1,2,3,4,5,6,7,8,9,10]);
        $result = $collection->chunk(3);
        self::assertEqualsCanonicalizing([1,2,3], $result[0]->all());
        self::assertEqualsCanonicalizing([4,5,6], $result[1]->all());
        self::assertEqualsCanonicalizing([7,8,9], $result[2]->all());
        self::assertEqualsCanonicalizing([10], $result[3]->all());
    }

    public function testFirst()
    {
        $collection = collect([1,2,3,4,5,6,7,8,9,10]);
        $result = $collection->first();
        self::assertEquals(1, $result);

        $result = $collection->first(function($value){
            return $value > 5;
        });
        self::assertEquals(6, $result);
    }
    public function testLast()
    {
        $collection = collect([1,2,3,4,5,6,7,8,9,10]);
        $result = $collection->last();
        self::assertEquals(10, $result);

        $result = $collection->last(function($value){
            return $value < 5;
        });
        self::assertEquals(4, $result);
    }

    public function testRandom()
    {
        $collection = collect([1,2,3,4,5,6,7,8,9,10]);
        $result1 = $collection->random();
        self::assertTrue(in_array($result1, [1,2,3,4,5,6,7,8,9,10]));
        $result2 = $collection->random(5);
        $this->assertInstanceOf(Collection::class, $result2);
        $this->assertCount(5, $result2);
        foreach ($result2 as $item) {
            $this->assertContains($item, $collection->all());
        }
    }

    public function testIsNotEmpty()
    {
        $collection = collect([1,2,3,4,5,6,7,8,9,10]);
        $result = $collection->isNotEmpty();
        self::assertTrue($result);
        $result = $collection->isEmpty();
        self::assertFalse($result);
        $result = $collection->contains(function($value){
            return $value;
        });
        self::assertTrue($result);
        $result = $collection->containsOneItem(); // mengecek apakah collection hanya memiliki 1 data
        self::assertFalse($result);
    }

    public function testOrdering()
    {
        $collection = collect([3,2,1,4,6,7,8,9,5,10]);
        $result = $collection->sort();
        self::assertEqualsCanonicalizing([1,2,3,4,5,6,7,8,9,10], $result->all());
        // echo $result->all()[0];
        $result = $collection->sortDesc();
        self::assertEqualsCanonicalizing([10,9,8,7,6,5,4,3,2,1], $result->all());
        // var_dump($result);
        $result = $collection->reverse();// reverse berdasarkan indeks
        self::assertEqualsCanonicalizing([10,5,9,8,7,6,4,1,2,3], $result->all());
        // var_dump($result);
    }

    public function testAgregate()
    {
        $collection = collect([1,2,3,4,5,6,7,8,9,10]);
        $result = $collection->sum();
        self::assertEquals(55, $result);
        $result = $collection->max();
        self::assertEquals(10, $result);
        $result = $collection->average();
        // var_dump($result);
        self::assertEquals(5.5, $result);
    }

    public function testReduce()
    {
        $collection = collect([1,2,3,4,5,6,7,8,9,10]);
        $result = $collection->reduce(function($carry, $item):int{
            return $carry * $item;
        },1);
        self::assertEquals(3628800, $result);
    }

    public function testLazyCollection()
    {
        $collection = LazyCollection::make(function(){
            $value = 1;
            while(true){
                yield $value;
                $value++;
            }
        });
        $result = $collection->take(10);
        self::assertEquals([1,2,3,4,5,6,7,8,9,10], $result->all());
        $result = $collection->take(20);
        self::assertEquals([1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20], $result->all());
    }

}
