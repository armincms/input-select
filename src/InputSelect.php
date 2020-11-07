<?php

namespace Armincms\Fields;

use Illuminate\Support\Str;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\Field;

class InputSelect extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'input-select';  

    /**
     * The callback to be used to resolve the field's display input value.
     *
     * @var \Closure
     */
    public $displayInputCallback;

    /**
     * The callback to be used to resolve the field's display select value.
     *
     * @var \Closure
     */
    public $displaySelectCallback;

    /**
     * The key name of the input field.
     *
     * @var string
     */
    public $input = 'input';

    /**
     * The key name of the select field.
     *
     * @var string
     */
    public $select = 'select';

    /**
     * The key name of the select field.
     *
     * @var string
     */
    public $default = [];

    /**
     * Create a new field.
     *
     * @param  string  $name
     * @param  string|callable|null  $attribute
     * @param  callable|null  $resolveCallback
     * @return void
     */
    public function __construct($name, $attribute = null, callable $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->displayUsing(function($value) { 
            $input = $this->display('input', $value);
            $select = $this->display('select', $value); 

            return "{$input} [ {$select} ]";
        });
    }

    /**
     * Resolve the field's value.
     *
     * @param  mixed  $resource
     * @param  string|null  $attribute
     * @return void
     */
    public function resolve($resource, $attribute = null)
    {
        parent::resolve($resource, $attribute);

        $this->value = collect($this->default)->merge($this->value)->only([$this->select, $this->input])->all();
    }

    /**
     * Get display value of an item.
     * 
     * @param  string $item 
     * @param  array $value     
     * @return mixed            
     */
    protected function display($item, $value)
    {
        $displayCallback = 'display' .Str::studly($item). 'Callback';

        return is_callable($this->{$displayCallback}) 
                    ? call_user_func($this->{$displayCallback}, $value) 
                    : $this->resolveAttribute($value, $this->{$item});
    } 

    /**
     * Define the callback that should be used to display the field's input value.
     *
     * @param  callable  $displayInputCallback
     * @return $this
     */
    public function displayInputUsing(callable $displayInputCallback)
    {
        $this->displayInputCallback = $displayInputCallback;

        return $this;
    }

    /**
     * Define the callback that should be used to display the field's select value.
     *
     * @param  callable  $displaySelectCallback
     * @return $this
     */
    public function displaySelectUsing(callable $displaySelectCallback)
    {
        $this->displaySelectCallback = $displaySelectCallback;

        return $this;
    }

    /**
     * Customize the input name.
     * 
     * @param  string $name   
     * @param  mixed $default
     * @return $this         
     */
    public function input(string $name, $default = null)
    {
        $this->input = $name;

        return $this->inputValue($default); 
    }

    /**
     * Make default value for the input.
     * 
     * @param  mixed $value
     * @return [type]       
     */
    public function inputValue($value = null)
    { 
        data_set($this->default, $this->input, $value);

        return $this;
    } 

    /**
     * Customize the select name.
     * 
     * @param  string $name   
     * @param  mixed $default
     * @return $this         
     */
    public function select(string $name, $default = null)
    {
        $this->select = $name;

        return $this->selectValue($default); 
    } 

    /**
     * Make default value for the select.
     * 
     * @param  mixed $value
     * @return [type]       
     */
    public function selectValue($value='')
    {
        data_set($this->default, $this->select, $value); 

        return $this;
    }  

    /**
     * Set the options for the select menu.
     *
     * @param  array|\Closure|\Illuminate\Support\Collection
     * @return $this
     */
    public function options($options)
    {
        if (is_callable($options)) {
            $options = $options();
        }

        return $this->withMeta([
            'options' => collect($options ?? [])->map(function ($label, $value) {
                return is_array($label) ? $label + ['value' => $value] : ['label' => $label, 'value' => $value];
            })->values()->all(),
        ]);
    }

    /**
     * Display values using their corresponding specified labels.
     *
     * @return $this
     */
    public function displayUsingLabels()
    {
        return $this->displaySelectUsing(function ($value) {  
            return collect($this->meta['options'])
                        ->where('value', $value[$this->select] ?? null)
                        ->first()['label'] ?? $this->display('select', $value); 
        });
    }  

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $requestAttribute
     * @param  object  $model
     * @param  string  $attribute
     * @return void
     */
    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        if ($request->exists($requestAttribute)) {
            $model->{$attribute} = json_decode($request[$requestAttribute], true);
        }
    } 

    /**
     * Prepare the field for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'select' => $this->select,
            'input'  => $this->input,  
        ]);
    }
}
