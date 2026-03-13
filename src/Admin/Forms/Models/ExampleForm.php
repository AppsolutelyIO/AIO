<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Forms\Models;

use Appsolutely\AIO\Models\Example;

/**
 * Example form demonstrating how to use the improved ModelForm base class
 *
 * Key features:
 * 1. Define relationships in constructor
 * 2. Implement initializeModel() method
 * 3. Relationships are automatically synced on create/update
 * 4. Form is automatically filled with relationship data on edit
 */
class ExampleForm extends ModelForm
{
    public function __construct(?int $id = null)
    {
        // Define which relationships should be synced
        $this->relationships = ['tags', 'categories'];
        parent::__construct($id);
    }

    protected function initializeModel(): void
    {
        $this->model = new Example();
    }

    public function form(): void
    {
        parent::form();

        $this->hidden('id');

        // Basic fields
        $this->text('title', 'Title')->required();
        $this->textarea('description', 'Description');
        $this->switch('status', 'Status')->default(true);

        // Relationship fields
        $this->multipleSelect('tags', 'Tags')
            ->options(Tag::status()->pluck('name', 'id'))
            ->customFormat(extract_values());

        $this->multipleSelect('categories', 'Categories')
            ->options(Category::status()->pluck('name', 'id'))
            ->customFormat(extract_values());
    }
}
