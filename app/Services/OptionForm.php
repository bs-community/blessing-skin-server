<?php

namespace App\Services;

use Option;
use ReflectionClass;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use BadMethodCallException;

class OptionForm
{
    /**
     * Pass this value to tell generator to
     * load text from language files automatically.
     */
    const AUTO_DETECT = 0x97ab1;

    protected $id;
    protected $title;

    protected $hint;
    protected $type  = 'primary';
    protected $items = [];

    protected $values = [];

    protected $buttons  = [];
    protected $messages = [];

    protected $alwaysCallback = null;

    protected $renderWithOutTable  = false;
    protected $renderInputTagsOnly = false;
    protected $renderWithOutSubmitButton = false;

    /**
     * Create a new option form instance.
     *
     * @param  string  $id
     * @param  string  $title
     * @return void
     */
    public function __construct($id, $title)
    {
        $this->id = $id;

        if ($title == self::AUTO_DETECT) {
            $this->title = trans("options.$this->id.title");
        } else {
            $this->title = $title;
        }
    }

    /**
     * Add option item to the form dynamically.
     *
     * @param  string  $method
     * @param  array   $params
     * @return OptionItem
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $params)
    {
        if (!in_array($method, ['text', 'checkbox', 'textarea', 'select', 'group'])) {
            throw new BadMethodCallException("Method [$method] does not exist on option form.");
        }

        if (!isset($params[1]) || Arr::get($params, 1) == OptionForm::AUTO_DETECT) {
            $params[1] = Arr::get(trans("options.$params[0]"), 'title', trans("options.$params[0]"));
        }

        $class = new ReflectionClass('App\Services\OptionForm'.Str::title($method));
        // use ReflectionClass to create a new OptionFormItem instance
        $item = $class->newInstanceArgs($params);
        $this->items[] = $item;

        return $item;
    }

    /**
     * Set the box type of option form.
     *
     * @param  string  $type
     * @return $this
     */
    public function type($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Add a hint to option form.
     *
     * @param  array  $info
     * @return $this
     */
    public function hint($hintContent)
    {
        if ($hintContent == self::AUTO_DETECT) {
            $hintContent = trans("options.$this->id.hint");
        }

        $this->hint = view('vendor.option-form.hint')->with('hint', $hintContent)->render();

        return $this;
    }

    /**
     * Add a piece of data to the option form.
     *
     * @param  string|array  $key
     * @param  mixed   $value
     * @return $this
     */
    public function with($key, $value = null)
    {
        if (is_array($key)) {
            $this->values = array_merge($this->values, $key);
        } else {
            $this->values[$key] = $value;
        }

        return $this;
    }

    /**
     * Add a button at the footer of option form.
     *
     * @param  array  $info
     * @return $this
     */
    public function addButton(array $info)
    {
        $info = array_merge([
            'style' => 'default',
            'class' => [],
            'href'  => '',
            'text'  => 'BUTTON',
            'type'  => 'button',
            'name'  => ''
        ], $info);

        $classes = "btn btn-{$info['style']} ".implode(' ', (array) Arr::get($info, 'class'));

        if ($info['href']) {
            $this->buttons[] = "<a href='{$info['href']}' class='$classes'>{$info['text']}</a>";
        } else {
            $this->buttons[] = "<button type='{$info['type']}' name='{$info['name']}' class='$classes'>{$info['text']}</button>";
        }

        return $this;
    }

    /**
     * Add a message to the top of option form.
     *
     * @param  string $msg
     * @param  string $style
     * @return $this
     */
    public function addMessage($msg, $style = "info")
    {
        if ($msg == self::AUTO_DETECT) {
            $msg = trans("options.$this->id.message");
        }

        $this->messages[] = "<div class='callout callout-$style'>$msg</div>";

        return $this;
    }

    /**
     * Add callback which will be always executed.
     *
     * @param  callable $callback
     * @return $this
     */
    public function always(callable $callback)
    {
        $this->alwaysCallback = $callback;

        return $this;
    }

    /**
     * Parse id formatted as *[*]. Return id & offset when succeed.
     *
     * @param  string $id
     * @return bool|array
     */
    protected function parseIdWithOffset($id)
    {
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
     * Handle the HTTP post request and update modified options.
     *
     * @param  callable $callback
     * @return $this
     */
    public function handle(callable $callback = null)
    {
        if (Arr::get($_POST, 'option') == $this->id) {
            if (!is_null($callback)) {
                call_user_func($callback, $this);
            }

            $postOptionQueue  = [];
            $arrayOptionQueue = [];

            foreach ($this->items as $item) {
                if ($item instanceof OptionFormGroup) {
                    foreach ($item->items as $innerItem) {
                        if ($innerItem['type'] == "text") {
                            $postOptionQueue[] = new OptionFormText($innerItem['id']);
                        }
                    }
                    continue;
                }
                // push item to the queue
                $postOptionQueue[] = $item;
            }

            foreach ($postOptionQueue as $item) {
                if ($item instanceof OptionFormCheckbox && !isset($_POST[$item->id])) {
                    // preset value for checkboxes which are not checked
                    $_POST[$item->id] = "false";
                }

                // Str::is('*[*]', $item->id)
                if (false !== ($result = $this->parseIdWithOffset($item->id))) {
                    // Push array option value to cache.
                    // Values of post ids like *[*] is collected as arrays in $_POST
                    // automatically by Laravel.
                    $arrayOptionQueue[$result['id']] = $_POST[$result['id']];
                    continue;
                }

                if (($data = Arr::get($_POST, $item->id)) != option($item->id, null, false)) {
                    Option::set($item->id, $data);
                }
            }

            foreach ($arrayOptionQueue as $key => $value) {
                Option::set($key, serialize($value));
            }

            $this->addMessage(trans('options.option-saved'), 'success');
        }

        return $this;
    }

    /**
     * Load value from $this->values & options by given id.
     *
     * @param  string $id
     * @return mixed
     */
    protected function getValueById($id)
    {
        if (false === ($result = $this->parseIdWithOffset($id))) {
            return Arr::get($this->values, $id, option($id));
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

    /**
     * Assign value for option items whose value haven't been set.
     *
     * @return void
     */
    protected function assignValues()
    {
        // load values for items if not set manually
        foreach ($this->items as $item) {
            if ($item instanceof OptionFormGroup) {
                foreach ($item->items as &$groupItem) {
                    if ($groupItem['id'] && is_null($groupItem['value'])) {
                        $groupItem['value'] = $this->getValueById($groupItem['id']);
                    }
                }
                continue;
            }

            if (is_null($item->value)) {
                $item->value = $this->getValueById($item->id);
            }
        }
    }

    public function renderWithOutTable()
    {
        $this->renderWithOutTable = true;

        return $this;
    }

    public function renderInputTagsOnly()
    {
        $this->renderInputTagsOnly = true;

        return $this;
    }

    public function renderWithOutSubmitButton()
    {
        $this->renderWithOutSubmitButton = true;

        return $this;
    }

    /**
     * Get the string contents of the option form.
     *
     * @return string
     */
    public function render()
    {
        if (!is_null($this->alwaysCallback)) {
            call_user_func($this->alwaysCallback, $this);
        }

        // attach submit button to the form
        if (!$this->renderWithOutSubmitButton) {
            $this->addButton([
                'style' => 'primary',
                'text'  => trans('general.submit'),
                'type'  => 'submit',
                'name'  => 'submit'
            ]);
        }

        $this->assignValues();

        return view('vendor.option-form.main')->with(array_merge(get_object_vars($this)))->render();
    }

    /**
     * Get the string contents of the option form.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}

class OptionFormItem
{
    public $id;

    public $name;

    public $hint;

    public $value = null;

    public $disabled;

    public $description;

    public function __construct($id, $name = null)
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
        if ($hintContent == OptionForm::AUTO_DETECT) {
            $hintContent = trans("options.$this->id.hint");
        }

        $this->hint = view('vendor.option-form.hint')->with('hint', $hintContent)->render();

        return $this;
    }

    public function disabled($disabled = "disabled")
    {
        $this->disabled = "disabled=\"$disabled\"";

        return $this;
    }

    public function description($description)
    {
        if ($description == OptionForm::AUTO_DETECT) {
            $description = trans("options.$this->id.description");
        }

        $this->description = $description;

        return $this;
    }

    /**
     * Render option item. Should be extended.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return;
    }

}

class OptionFormText extends OptionFormItem
{
    public function render()
    {
        return view('vendor.option-form.text')->with([
            'id' => $this->id,
            'value' => $this->value,
            'disabled' => $this->disabled
        ]);
    }
}

class OptionFormCheckbox extends OptionFormItem
{
    protected $label;

    public function label($label)
    {
        if ($label == OptionForm::AUTO_DETECT) {
            $label = trans("options.$this->id.label");
        }

        $this->label = $label;

        return $this;
    }

    public function render()
    {
        return view('vendor.option-form.checkbox')->with([
            'id'    => $this->id,
            'value' => $this->value,
            'label' => $this->label,
            'disabled' => $this->disabled
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
            'value' => $this->value,
            'disabled' => $this->disabled
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
            'selected' => $this->value,
            'disabled' => $this->disabled
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
        if ($value == OptionForm::AUTO_DETECT) {
            $value = trans("options.$this->id.addon");
        }

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
