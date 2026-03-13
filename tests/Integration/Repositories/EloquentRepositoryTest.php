<?php

namespace Appsolutely\AIO\Tests\Integration\Repositories;

use Appsolutely\AIO\Repositories\EloquentRepository;
use Appsolutely\AIO\Tests\Integration\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TestModel extends Model
{
    protected $table = 'test_items';
    protected $guarded = [];
    public $timestamps = true;
}

class TestParentModel extends Model
{
    protected $table = 'test_parents';
    protected $guarded = [];

    public function profile(): HasOne
    {
        return $this->hasOne(TestChildModel::class, 'parent_id');
    }
}

class TestChildModel extends Model
{
    protected $table = 'test_children';
    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(TestParentModel::class, 'parent_id');
    }
}

class EloquentRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('test_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('test_parents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('test_children', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id');
            $table->string('name');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_children');
        Schema::dropIfExists('test_parents');
        Schema::dropIfExists('test_items');
        parent::tearDown();
    }

    // --- Construction ---

    public function test_create_from_model_instance()
    {
        $model = new TestModel();
        $repo = new EloquentRepository($model);

        $this->assertSame('id', $repo->getKeyName());
        $this->assertInstanceOf(TestModel::class, $repo->model());
    }

    public function test_create_from_class_string()
    {
        $repo = new EloquentRepository(TestModel::class);

        $this->assertSame('id', $repo->getKeyName());
        $this->assertInstanceOf(TestModel::class, $repo->model());
    }

    public function test_create_from_builder()
    {
        $builder = TestModel::where('status', 'active');
        $repo = new EloquentRepository($builder);

        $this->assertInstanceOf(TestModel::class, $repo->model());
    }

    // --- Key name ---

    public function test_get_key_name()
    {
        $repo = new EloquentRepository(TestModel::class);
        $this->assertSame('id', $repo->getKeyName());
    }

    public function test_set_key_name()
    {
        $repo = new EloquentRepository(TestModel::class);
        $repo->setKeyName('uuid');
        $this->assertSame('uuid', $repo->getKeyName());
    }

    // --- Soft deletes ---

    public function test_soft_deletes_false_by_default()
    {
        $repo = new EloquentRepository(TestModel::class);
        $this->assertFalse($repo->isSoftDeletes());
    }

    // --- Timestamp columns ---

    public function test_created_at_column()
    {
        $repo = new EloquentRepository(TestModel::class);
        $this->assertSame('created_at', $repo->getCreatedAtColumn());
    }

    public function test_updated_at_column()
    {
        $repo = new EloquentRepository(TestModel::class);
        $this->assertSame('updated_at', $repo->getUpdatedAtColumn());
    }

    // --- Column queries ---

    public function test_grid_columns_default()
    {
        $repo = new EloquentRepository(TestModel::class);
        $this->assertSame(['*'], $repo->getGridColumns());
    }

    public function test_form_columns_default()
    {
        $repo = new EloquentRepository(TestModel::class);
        $this->assertSame(['*'], $repo->getFormColumns());
    }

    public function test_detail_columns_default()
    {
        $repo = new EloquentRepository(TestModel::class);
        $this->assertSame(['*'], $repo->getDetailColumns());
    }

    // --- Relations ---

    public function test_set_relations()
    {
        $repo = new EloquentRepository(TestModel::class);
        $repo->setRelations(['comments', 'tags']);

        $this->assertSame(['comments', 'tags'], $repo->getRelations());
    }

    public function test_set_relations_via_constructor()
    {
        $repo = new EloquentRepository(TestModel::class);
        $repo->setRelations(['posts']);
        $this->assertSame(['posts'], $repo->getRelations());
    }

    public function test_create_model()
    {
        $repo = new EloquentRepository(TestModel::class);
        $model = $repo->createModel(['name' => 'Test']);

        $this->assertInstanceOf(TestModel::class, $model);
        $this->assertSame('Test', $model->name);
    }

    // --- Make factory ---

    public function test_make_from_model()
    {
        $repo = EloquentRepository::make(new TestModel());
        $this->assertInstanceOf(EloquentRepository::class, $repo);
        $this->assertInstanceOf(TestModel::class, $repo->model());
    }

    // --- joinParameters ---

    public function test_join_parameters_belongs_to()
    {
        $child = new TestChildModel();
        $relation = $child->parent();

        $repo = new EloquentRepository(TestChildModel::class);
        $method = new \ReflectionMethod($repo, 'joinParameters');
        $result = $method->invoke($repo, $relation);

        $this->assertCount(4, $result);
        $this->assertSame('test_parents', $result[0]);
        $this->assertSame('parent_id', $result[1]);
        $this->assertSame('=', $result[2]);
        $this->assertSame('test_parents.id', $result[3]);
    }

    public function test_join_parameters_has_one()
    {
        $parent = new TestParentModel();
        $relation = $parent->profile();

        $repo = new EloquentRepository(TestParentModel::class);
        $method = new \ReflectionMethod($repo, 'joinParameters');
        $result = $method->invoke($repo, $relation);

        $this->assertCount(4, $result);
        $this->assertSame('test_children', $result[0]);
        $this->assertSame('=', $result[2]);
    }
}
