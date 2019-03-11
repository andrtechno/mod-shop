<?php

namespace panix\mod\shop\components;

use panix\engine\Html;
use yii\base\Model;
use yii\grid\DataColumn;

/**
 *
 *
 * [
 * 'class' => 'panix\mod\shop\components\EavColumns',
 * 'attribute' => 'eav_size',
 * 'header' => 'Размеры',
 * 'contentOptions' => ['class' => 'text-center']
 * ];
 *
 *
 */
class EavColumn extends DataColumn
{

    public $format = 'raw';


    /**
     * {@inheritdoc}
     */
    protected function renderFilterCellContent()
    {
        if (is_string($this->filter)) {
            return $this->filter;
        }

        $model = $this->grid->filterModel;

        if ($this->filter !== false && $model instanceof Model && $this->attribute !== null && $model->isAttributeActive($this->attribute)) {
            if ($model->hasErrors($this->attribute)) {
                Html::addCssClass($this->filterOptions, 'has-error');
                $error = ' ' . Html::error($model, $this->attribute, $this->grid->filterErrorOptions);
            } else {
                $error = '';
            }
            if (is_array($this->filter)) {
                $options = array_merge(['prompt' => ''], $this->filterInputOptions);
                return Html::activeDropDownList($model, $this->attribute, $this->filter, $options) . $error;
            } elseif ($this->format === 'boolean') {
                $options = array_merge(['prompt' => ''], $this->filterInputOptions);
                return Html::activeDropDownList($model, $this->attribute, [
                        1 => $this->grid->formatter->booleanFormat[1],
                        0 => $this->grid->formatter->booleanFormat[0],
                    ], $options) . $error;
            }

            return Html::activeTextInput($model, $this->attribute, $this->filterInputOptions) . $error;
        }

        return parent::renderFilterCellContent();
    }


}
