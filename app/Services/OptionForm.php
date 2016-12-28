<?php

namespace App\Services;

use Option;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class OptionForm
{
    public $id;
    public $title;

    protected $hint;
    protected $type = 'primary';
    protected $items;

    protected $values;

    protected $messages = [];

    protected $alwaysCallback = null;

    public function __construct($id, $title)
    {
        $this->id    = $id;
        $this->title = $title;
    }

    public function __call($name, $arguments)
    {
        if (!in_array($name, ['text', 'checkbox', 'textarea', 'select', 'group'])) {
            throw new \InvalidArgumentException("No such item for option form.", 1);
        }

        $class = new \ReflectionClass('App\Services\OptionForm'.Str::title($name));
        // use ReflectionClass to create a new OptionFormItem instance
        $item = $class->newInstanceArgs($arguments);
        $this->items[] = $item;

        return $item;
    }

    public function type($type)
    {
        $this->type = $type;

        return $this;
    }

    public function hint($hint_content)
    {
        $this->hint = view('vendor.option-form.hint')->with('hint', $hint_content)->render();

        return $this;
    }

    public function setValues(array $values)
    {
        $this->values = array_merge($this->values, $values);

        return $this;
    }

    public function addMessage($msg, $type = "info")
    {
        $this->messages[] = "<div class='callout callout-$type'>$msg</div>";
    }

    public function handle($callback = null)
    {
        if (Arr::get($_POST, 'option') == $this->id) {
            if (!is_null($callback)) {
                call_user_func($callback, $this);
            }

            $arrayOptionCache = [];

            foreach ($this->items as $item) {
                if ($item instanceof OptionFormCheckbox && !isset($_POST[$item->id])) {
                    // preset value for checkboxes which are not checked
                    $_POST[$item->id] = "0";
                }

                // Str::is('*[*]', $item->id)
                if (false !== ($result = $this->parseIdWithOffset($item->id))) {
                    // push array option value to cache
                    $arrayOptionCache[$result['id']][$result['offset']] = $_POST[$item->id];
                    continue;
                }

                if ($_POST[$item->id] != option($item->id, null, false)) {
                    Option::set($item->id, $_POST[$item->id]);
                }
            }

            foreach ($arrayOptionCache as $key => $value) {
                Option::set($key, serialize($value));
            }

            $this->addMessage('设置已保存。', 'success');
        }

        return $this;
    }

    public function always($callback)
    {
        $this->alwaysCallback = $callback;

        return $this;
    }

    protected function parseIdWithOffset($id)
    {
        // detect if id is formatted as *[*]
        // array option is stored as unserialized string
        preg_match('/(.*)\[(.*)\]/', $id, $matches);

        if (isset($matches[2])) {
            return [
                'id'     => $matches[1],
                'offset' => $matches[2]
            ];
        }

        return false;
    }

    /**
     * Load value from $this->values & options.
     *
     * @param  string $id
     * @return mixed
     */
    protected function loadValueFromId($id)
    {
        if (false === ($result = $this->parseIdWithOffset($id))) {
            return option($id);
        } else {
            $option = Arr::get(
                $this->values,
                $result['id'],
                // fallback to load from options
                @unserialize(option($result['id']))
            );

            return Arr::get($option, $result['offset']);
        }
    }

    public function render()
    {
        if (!is_null($this->alwaysCallback)) {
            call_user_func($this->alwaysCallback, $this);
        }

        // load values for items if not set manually
        foreach ($this->items as $item) {
            if ($item instanceof OptionFormGroup) {
                foreach ($item->items as $groupItem) {
                    if ($groupItem['id'] && is_null($groupItem['value'])) {
                        $groupItem['value'] = $this->loadValueFromId($groupItem['id']);
                    }
                }
                continue;
            }

            if (is_null($item->value)) {
                $item->value = $this->loadValueFromId($item->id);
            }
        }

        return view('vendor.option-form.main')->with(get_object_vars($this))->render();
    }
}

class OptionFormItem
{
    public $id;

    public $name;

    public $value = null;

    public $hint;

    public $description;

    public function __construct($id, $name)
    {
        $this->id   = $id;
        $this->name = $name;
    }

    public function value($value)
    {
        $this->value = $value;

        return $this;
    }

    public function hint($hintContent)
    {
        $this->hint = view('vendor.option-form.hint')->with('hint', $hintContent)->render();

        return $this;
    }

    public function description($description)
    {
        $this->description = $description;

        return $this;
    }

    public function render()
    {
        //
    }

}

class OptionFormText extends OptionFormItem
{
    public function render()
    {
        return view('vendor.option-form.text')->with([
            'id'    => $this->id,
            'value' => $this->value
        ]);
    }
}

class OptionFormCheckbox extends OptionFormItem
{
    protected $label;

    public function label($label)
    {
        $this->label = $label;

        return $this;
    }

    public function render()
    {
        return view('vendor.option-form.checkbox')->with([
            'id'    => $this->id,
            'value' => $this->value,
            'label' => $this->label
        ]);
    }
}

class OptionFormTextarea extends OptionFormItem
{
    protected $rows = 3;

    public function rows($rows)
    {
        $this->rows = $rows;

        return $this;
    }

    public function render()
    {
        return view('vendor.option-form.textarea')->with([
            'id'    => $this->id,
            'rows'  => $this->rows,
            'value' => $this->value
        ]);
    }
}

class OptionFormSelect extends OptionFormItem
{
    protected $options;

    public function option($value, $name)
    {
        $this->options[] = compact('value', 'name');

        return $this;
    }

    public function render()
    {
        return view('vendor.option-form.select')->with([
            'id'       => $this->id,
            'options'  => $this->options,
            'selected' => $this->value
        ]);
    }
}

class OptionFormGroup extends OptionFormItem
{
    public $items = [];

    public function text($id, $value = null)
    {
        $this->items[] = ['type' => 'text', 'id' => $id, 'value' => $value];

        return $this;
    }

    public function addon($value)
    {
        $this->items[] = ['type' => 'addon', 'id' => null, 'value' => $value];

        return $this;
    }

    public function render()
    {
        $rendered = [];

        foreach ($this->items as $item) {
            if ($item['id'] && is_null($item['value'])) {
                $item['value'] = option($item['id'], null, false);
            }

            $rendered[] = view('vendor.option-form.'.$item['type'])->with([
                'id'    => $item['id'],
                'value' => $item['value']
            ]);
        }

        return view('vendor.option-form.group')->with('items', $rendered);
    }
}
