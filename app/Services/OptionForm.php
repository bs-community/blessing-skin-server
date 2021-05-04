<?php

namespace App\Services;

use BadMethodCallException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Option;
use ReflectionClass;

/**
 * @method OptionFormText     text(string $id, string|null $name)
 * @method OptionFormCheckbox checkbox(string $id, string|null $name)
 * @method OptionFormTextarea textarea(string $id, string|null $name)
 * @method OptionFormSelect   select(string $id, string|null $name)
 * @method OptionFormGroup    group(string $id, string|null $name)
 */
class OptionForm
{
    /**
     * Pass this value to tell generator to
     * load text from language files automatically.
     */
    public const AUTO_DETECT = 0x97ab1;

    protected $id;
    protected $title;

    protected $hint;
    protected $type = 'primary';
    protected $items = [];

    protected $values = [];

    protected $buttons = [];
    protected $messages = [];
    protected $alerts = [];

    protected $hookBefore;
    protected $hookAfter;
    protected $alwaysCallback = null;

    protected $renderWithoutTable = false;
    protected $renderInputTagsOnly = false;
    protected $renderWithoutSubmitButton = false;

    /**
     * Create a new option form instance.
     */
    public function __construct(string $id, string $title = self::AUTO_DETECT)
    {
        $this->id = $id;

        if ($title == self::AUTO_DETECT) {
            $this->title = trans("options.$id.title");
        } else {
            $this->title = $title;
        }
    }

    /**
     * @throws \BadMethodCallException
     */
    public function __call(string $method, array $params): OptionFormItem
    {
        if (!in_array($method, ['text', 'checkbox', 'textarea', 'select', 'group'])) {
            throw new BadMethodCallException("Method [$method] does not exist on option form.");
        }

        // Assign name for option item
        if (!isset($params[1]) || Arr::get($params, 1) == OptionForm::AUTO_DETECT) {
            $params[1] = Arr::get(trans("options.$this->id.$params[0]"), 'title', trans("options.$this->id.$params[0]"));
        }

        $class = new ReflectionClass('App\Services\OptionForm'.Str::title($method));
        // Use ReflectionClass to create a new OptionFormItem instance
        $item = $class->newInstanceArgs($params);
        $item->setParentId($this->id);
        $this->items[] = $item;

        return $item;
    }

    /** Set the box type of option form. */
    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /** Add a hint to option form. */
    public function hint($hintContent = self::AUTO_DETECT): self
    {
        if ($hintContent == self::AUTO_DETECT) {
            $hintContent = trans("options.$this->id.hint");
        }

        $this->hint = view('forms.hint')->with('hint', $hintContent)->render();

        return $this;
    }

    /**
     * Add a piece of data to the option form.
     *
     * @param string|array $key
     * @param mixed        $value
     */
    public function with($key, $value = null): self
    {
        if (is_array($key)) {
            $this->values = array_merge($this->values, $key);
        } else {
            $this->values[$key] = $value;
        }

        return $this;
    }

    /** Add a button at the footer of option form. */
    public function addButton(array $info): self
    {
        $info = array_merge([
            'style' => 'default',
            'class' => [],
            'href' => '',
            'text' => 'BUTTON',
            'type' => 'button',
            'name' => '',
        ], $info);

        $info['class'] = array_merge(
            ['btn', 'btn-'.$info['style']],
            (array) Arr::get($info, 'class')
        );
        $this->buttons[] = $info;

        return $this;
    }

    /**
     * Add a message to the top of option form.
     *
     * @param string $msg
     */
    public function addMessage($msg = self::AUTO_DETECT, string $style = 'info'): self
    {
        if ($msg == self::AUTO_DETECT) {
            $msg = trans("options.$this->id.message");
        }

        $this->messages[] = ['content' => $msg, 'type' => $style];

        return $this;
    }

    /**
     * Add an alert to the top of option form.
     *
     * @param string $msg
     */
    public function addAlert($msg = self::AUTO_DETECT, string $style = 'info'): self
    {
        if ($msg == self::AUTO_DETECT) {
            $msg = trans("options.$this->id.alert");
        }

        $this->alerts[] = ['content' => $msg, 'type' => $style];

        return $this;
    }

    /**
     * Add callback which will be executed before handling options.
     */
    public function before(callable $callback): self
    {
        $this->hookBefore = $callback;

        return $this;
    }

    /**
     * Add callback which will be executed after handling options.
     */
    public function after(callable $callback): self
    {
        $this->hookAfter = $callback;

        return $this;
    }

    /**
     * Add callback which will be always executed.
     */
    public function always(callable $callback): self
    {
        $this->alwaysCallback = $callback;

        return $this;
    }

    /**
     * Handle the HTTP post request and update modified options.
     */
    public function handle(callable $callback = null): self
    {
        $request = request();
        $allPostData = $request->all();

        if ($request->isMethod('POST') && Arr::get($allPostData, 'option') == $this->id) {
            if (!is_null($callback)) {
                call_user_func($callback, $this);
            }

            if (!is_null($this->hookBefore)) {
                call_user_func($this->hookBefore, $this);
            }

            $postOptionQueue = [];

            foreach ($this->items as $item) {
                if ($item instanceof OptionFormGroup) {
                    foreach ($item->items as $innerItem) {
                        if ($innerItem['type'] == 'text') {
                            $postOptionQueue[] = new OptionFormText($innerItem['id']);
                        }
                    }
                    continue;
                }
                // Push item to the queue
                $postOptionQueue[] = $item;
            }

            foreach ($postOptionQueue as $item) {
                if ($item instanceof OptionFormCheckbox && !isset($allPostData[$item->id])) {
                    // preset value for checkboxes which are not checked
                    $allPostData[$item->id] = false;
                }

                // Compare with raw option value
                if (($data = Arr::get($allPostData, $item->id)) != option($item->id, null, true)) {
                    $formatted = is_null($item->format) ? $data : call_user_func($item->format, $data);
                    Option::set($item->id, $formatted);
                }
            }

            if (!is_null($this->hookAfter)) {
                call_user_func($this->hookAfter, $this);
            }

            $this->addAlert(trans('options.option-saved'), 'success');
        }

        return $this;
    }

    /**
     * Load value from $this->values & options by given id.
     */
    protected function getValueById(string $id)
    {
        return Arr::get($this->values, $id, option_localized($id));
    }

    /**
     * Assign value for option items whose value haven't been set.
     */
    protected function assignValues(): void
    {
        // Load values for items if not set manually
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

    public function renderWithoutTable(): self
    {
        $this->renderWithoutTable = true;

        return $this;
    }

    public function renderInputTagsOnly(): self
    {
        $this->renderInputTagsOnly = true;

        return $this;
    }

    public function renderWithoutSubmitButton(): self
    {
        $this->renderWithoutSubmitButton = true;

        return $this;
    }

    /**
     * Get the string contents of the option form.
     */
    public function render(): string
    {
        if (!is_null($this->alwaysCallback)) {
            call_user_func($this->alwaysCallback, $this);
        }

        // attach submit button to the form
        if (!$this->renderWithoutSubmitButton) {
            $this->addButton([
                'style' => 'primary',
                'text' => trans('general.submit'),
                'type' => 'submit',
                'name' => 'submit_'.$this->id,
            ]);
        }

        $this->assignValues();

        return view('forms.form')
            ->with(get_object_vars($this))
            ->render();
    }

    /**
     * Get the string contents of the option form.
     */
    public function __toString(): string
    {
        return $this->render();
    }
}

class OptionFormItem
{
    public $id;

    public $name;

    public $hint;

    public $format;

    public $value = null;

    public $disabled;

    public $description;

    protected $parentId;

    public function __construct(string $id, $name = null)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function setParentId($id)
    {
        $this->parentId = $id;

        return $this;
    }

    public function value($value)
    {
        $this->value = $value;

        return $this;
    }

    public function hint($hintContent = OptionForm::AUTO_DETECT)
    {
        if ($hintContent == OptionForm::AUTO_DETECT) {
            $hintContent = trans("options.$this->parentId.$this->id.hint");
        }

        $this->hint = view('forms.hint')->with('hint', $hintContent)->render();

        return $this;
    }

    public function format(callable $callback)
    {
        $this->format = $callback;

        return $this;
    }

    public function disabled($disabled = 'disabled')
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function description($description = OptionForm::AUTO_DETECT)
    {
        if ($description == OptionForm::AUTO_DETECT) {
            $description = trans("options.$this->parentId.$this->id.description");
        }

        $this->description = $description;

        return $this;
    }
}

class OptionFormText extends OptionFormItem
{
    protected $placeholder = '';

    public function placeholder($placeholder = OptionForm::AUTO_DETECT)
    {
        if ($placeholder == OptionForm::AUTO_DETECT) {
            $key = "options.$this->parentId.$this->id.placeholder";
            $placeholder = trans()->has($key) ? trans($key) : '';
        }

        $this->placeholder = $placeholder;

        return $this;
    }

    public function render()
    {
        return view('forms.text')->with([
            'id' => $this->id,
            'value' => $this->value,
            'disabled' => $this->disabled,
            'placeholder' => $this->placeholder,
        ]);
    }
}

class OptionFormCheckbox extends OptionFormItem
{
    protected $label;

    public function label($label = OptionForm::AUTO_DETECT)
    {
        if ($label == OptionForm::AUTO_DETECT) {
            $label = trans("options.$this->parentId.$this->id.label");
        }

        $this->label = $label;

        return $this;
    }

    public function render()
    {
        return view('forms.checkbox')->with([
            'id' => $this->id,
            'value' => $this->value,
            'label' => $this->label,
            'disabled' => $this->disabled,
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
        return view('forms.textarea')->with([
            'id' => $this->id,
            'rows' => $this->rows,
            'value' => $this->value,
            'disabled' => $this->disabled,
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
        return view('forms.select')->with([
            'id' => $this->id,
            'options' => (array) $this->options,
            'selected' => $this->value,
            'disabled' => $this->disabled,
        ]);
    }
}

class OptionFormGroup extends OptionFormItem
{
    public $items = [];

    public function text($id, $value = null, $placeholder = OptionForm::AUTO_DETECT)
    {
        if ($placeholder == OptionForm::AUTO_DETECT) {
            $key = "options.$this->parentId.$this->id.placeholder";
            $placeholder = trans()->has($key) ? trans($key) : '';
        }

        $this->items[] = [
            'type' => 'text',
            'id' => $id,
            'value' => $value,
            'placeholder' => $placeholder,
        ];

        return $this;
    }

    public function addon($value = OptionForm::AUTO_DETECT)
    {
        if ($value == OptionForm::AUTO_DETECT) {
            $value = trans("options.$this->parentId.$this->id.addon");
        }

        $this->items[] = ['type' => 'addon', 'id' => null, 'value' => $value];

        return $this;
    }

    public function render()
    {
        $rendered = [];

        foreach ($this->items as $item) {
            $rendered[] = view('forms.'.$item['type'])->with([
                'id' => $item['id'],
                'value' => $item['value'],
                'placeholder' => Arr::get($item, 'placeholder'),
            ]);
        }

        return view('forms.group')->with('items', $rendered);
    }
}
