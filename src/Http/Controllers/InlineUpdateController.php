<?php

namespace Dcat\Admin\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Throwable;

class InlineUpdateController
{
    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request)
    {
        try {
            $modelClass = $request->input('model');
            $id = $request->input('id');
            $field = $request->input('field');
            $value = $request->input('value');

            if (! is_string($modelClass) || ! class_exists($modelClass)) {
                return $this->error('Invalid model class.');
            }

            if (! is_subclass_of($modelClass, Model::class)) {
                return $this->error('Invalid model type.');
            }

            if (! is_string($field) || $field === '') {
                return $this->error('Invalid field.');
            }

            /** @var Model $record */
            $record = (new $modelClass())->newQuery()->findOrFail($id);
            $record->{$field} = $value;
            $record->save();

            return response()->json([
                'status' => true,
                'message' => trans('admin.update_succeeded'),
                'data' => ['message' => trans('admin.update_succeeded')],
            ]);
        } catch (Throwable $e) {
            report($e);

            return $this->error($e->getMessage());
        }
    }

    /**
     * @param  string  $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error($message)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => ['message' => $message],
        ]);
    }
}
