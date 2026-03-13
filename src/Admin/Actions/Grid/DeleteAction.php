<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Actions\Grid;

use Appsolutely\AIO\Actions\Response;
use Appsolutely\AIO\Grid\RowAction;
use Appsolutely\AIO\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class DeleteAction extends RowAction
{
    /**
     * @return string
     */
    protected $title = 'Delete Action';

    /**
     * Handle the action request.
     *
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        // dump($this->getKey());
        $class = $request->get('class');
        if (! class_exists($class)
            || ! str_starts_with($class, 'App\\Models\\')
            || ! is_subclass_of($class, \Illuminate\Database\Eloquent\Model::class)
        ) {
            return $this->response()->error(__t('Failed to delete action'));
        }
        try {
            $record = (new $class())->find($this->getKey());
            if (! $record) {
                return $this->response()->error(__t('Record not found'));
            }
            $record->delete();

            return $this->response()
                ->success(__t('Processed successfully: ') . $this->getKey())
                ->refresh();
        } catch (\Exception $e) {
            log_error(__t('Delete Action Exception: ') . $e->getMessage());

            return $this->response()->error(__t('Delete Action Exception: ') . $e->getMessage());
        }
    }

    /**
     * @return string|array|void
     */
    public function confirm()
    {
        return [__t('Confirm?'), __t('Are you sure to delete this item?')];
    }

    /**
     * @param  Model|Authenticatable|HasPermissions|null  $user
     */
    protected function authorize($user): bool
    {
        return true;
    }

    /**
     * @return array
     */
    protected function parameters()
    {
        return [
            'class' => get_class($this->row),
        ];
    }

    public function title()
    {
        return admin_delete_action();
    }
}
