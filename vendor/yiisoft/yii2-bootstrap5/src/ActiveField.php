<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\bootstrap5;

use yii\helpers\ArrayHelper;

/**
 * A Bootstrap 5 enhanced version of [[\yii\widgets\ActiveField]].
 *
 * This class adds some useful features to [[\yii\widgets\ActiveField|ActiveField]] to render all
 * sorts of Bootstrap 5 form fields in different form layouts:
 *
 * - [[inputTemplate]] is an optional template to render complex inputs, for example input groups
 * - [[horizontalCssClasses]] defines the CSS grid classes to add to label, wrapper, error and hint
 *   in horizontal forms
 * - [[inline]]/[[inline()]] is used to render inline [[checkboxList()]] and [[radioList()]]
 * - [[enableError]] can be set to `false` to disable to the error
 * - [[enableLabel]] can be set to `false` to disable to the label
 * - [[label()]] can be used with a `bool` argument to enable/disable the label
 *
 * There are also some new placeholders that you can use in the [[template]] configuration:
 *
 * - `{beginLabel}`: the opening label tag
 * - `{labelTitle}`: the label title for use with `{beginLabel}`/`{endLabel}`
 * - `{endLabel}`: the closing label tag
 * - `{beginWrapper}`: the opening wrapper tag
 * - `{endWrapper}`: the closing wrapper tag
 *
 * The wrapper tag is only used for some layouts and form elements.
 *
 * Note that some elements use slightly different defaults for [[template]] and other options.
 * You may want to override those predefined templates for checkboxes, radio buttons, checkboxLists
 * and radioLists in the [[\yii\widgets\ActiveForm::fieldConfig|fieldConfig]] of the
 * [[\yii\widgets\ActiveForm]]:
 *
 * - [[checkTemplate]] the default template for checkboxes and radios
 * - [[radioTemplate]] the template for radio buttons in default layout
 * - [[checkHorizontalTemplate]] the template for checkboxes in horizontal layout
 * - [[radioHorizontalTemplate]] the template for radio buttons in horizontal layout
 * - [[checkEnclosedTemplate]] the template for checkboxes and radios enclosed by label
 *
 * Example:
 *
 * ```php
 * use yii\bootstrap5\ActiveForm;
 *
 * $form = ActiveForm::begin(['layout' => 'horizontal']);
 *
 * // Form field without label
 * echo $form->field($model, 'demo', [
 *     'inputOptions' => [
 *         'placeholder' => $model->getAttributeLabel('demo'),
 *     ],
 * ])->label(false);
 *
 * // Inline radio list
 * echo $form->field($model, 'demo')->inline()->radioList($items);
 *
 * // Control sizing in horizontal mode
 * echo $form->field($model, 'demo', [
 *     'horizontalCssClasses' => [
 *         'wrapper' => 'col-sm-2',
 *     ]
 * ]);
 *
 * // With 'default' layout you would use 'template' to size a specific field:
 * echo $form->field($model, 'demo', [
 *     'template' => '{label} <div class="row"><div class="col-sm-4">{input}{error}{hint}</div></div>'
 * ]);
 *
 * // Input group
 * echo $form->field($model, 'demo', [
 *     'inputTemplate' => '<div class="input-group"><div class="input-group-prepend">
 *         <span class="input-group-text">@</span>
 *     </div>{input}</div>',
 * ]);
 *
 * ActiveForm::end();
 * ```
 *
 * @see ActiveForm
 * @see https://getbootstrap.com/docs/5.1/components/forms/
 *
 * @author Michael Härtl <haertl.mike@gmail.com>
 * @author Simon Karlen <simi.albi@outlook.com>
 */
class ActiveField extends \yii\widgets\ActiveField
{
    /**
     * @var bool whether to render [[checkboxList()]] and [[radioList()]] inline.
     */
    public $inline = false;
    /**
     * @var string|null optional template to render the `{input}` placeholder content
     */
    public $inputTemplate = null;
    /**
     * @var array options for the wrapper tag, used in the `{beginWrapper}` placeholder
     */
    public $wrapperOptions = [];
    /**
     * {@inheritdoc}
     */
    public $options = ['class' => ['widget' => 'mb-3']];
    /**
     * {@inheritdoc}
     */
    public $inputOptions = ['class' => ['widget' => 'form-control']];
    /**
     * @var array the default options for the input checkboxes. The parameter passed to individual
     * input methods (e.g. [[checkbox()]]) will be merged with this property when rendering the input tag.
     *
     * If you set a custom `id` for the input element, you may need to adjust the [[$selectors]] accordingly.
     *
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     * @since 2.0.7
     */
    public $checkOptions = [
        'class' => ['widget' => 'form-check-input'],
        'labelOptions' => [
            'class' => ['widget' => 'form-check-label'],
        ],
    ];
    /**
     * @var array the default options for the input radios. The parameter passed to individual
     * input methods (e.g. [[radio()]]) will be merged with this property when rendering the input tag.
     *
     * If you set a custom `id` for the input element, you may need to adjust the [[$selectors]] accordingly.
     *
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     * @since 2.0.7
     */
    public $radioOptions = [
        'class' => ['widget' => 'form-check-input'],
        'labelOptions' => [
            'class' => ['widget' => 'form-check-label'],
        ],
    ];
    /**
     * {@inheritdoc}
     */
    public $errorOptions = ['class' => 'invalid-feedback'];
    /**
     * {@inheritdoc}
     */
    public $labelOptions = ['class' => ['widget' => 'form-label']];
    /**
     * {@inheritdoc}
     */
    public $hintOptions = ['class' => ['widget' => 'form-text', 'text-muted'], 'tag' => 'div'];
    /**
     * @var null|array CSS grid classes for horizontal layout. This must be an array with these keys:
     *  - 'offset' the offset grid class to append to the wrapper if no label is rendered
     *  - 'label' the label grid class
     *  - 'wrapper' the wrapper grid class
     *  - 'error' the error grid class
     *  - 'hint' the hint grid class
     */
    public $horizontalCssClasses = [];
    /**
     * @var string the template for checkboxes in default layout
     */
    public $checkTemplate = "<div class=\"form-check\">\n{input}\n{label}\n{error}\n{hint}\n</div>";
    /**
     * @var string the template forswitches (custom checkboxes) in default layout
     */
    public $switchTemplate = "<div class=\"form-check form-switch\">\n{input}\n{label}\n{error}\n{hint}\n</div>";
    /**
     * @var string the template for radios in default layout
     * @since 2.0.5
     */
    public $radioTemplate = "<div class=\"form-check\">\n{input}\n{label}\n{error}\n{hint}\n</div>";
    /**
     * @var string the template for checkboxes and radios in horizontal layout
     */
    public $checkHorizontalTemplate = "{beginWrapper}\n<div class=\"form-check\">\n{input}\n{label}\n{error}\n{hint}\n</div>\n{endWrapper}";
    /**
     * @var string the template for switches (custom checkboxes) in horizontal layout
     */
    public $switchHorizontalTemplate = "{beginWrapper}\n<div class=\"form-check form-switch\">\n{input}\n{label}\n{error}\n{hint}\n</div>\n{endWrapper}";
    /**
     * @var string the template for checkboxes and radios in horizontal layout
     * @since 2.0.5
     */
    public $radioHorizontalTemplate = "{beginWrapper}\n<div class=\"form-check\">\n{input}\n{label}\n{error}\n{hint}\n</div>\n{endWrapper}";
    /**
     * @var string the `enclosed by label` template for checkboxes and radios in default layout
     */
    public $checkEnclosedTemplate = "<div class=\"form-check\">\n{beginLabel}\n{input}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>";
    /**
     * @var string tthe `enclosed by label` template for switches(custom checkboxes) in default layout
     */
    public $switchEnclosedTemplate = "<div class=\"form-check form-switch\">\n{beginLabel}\n{input}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>";
    /**
     * @var bool whether to render the error. Default is `true` except for layout `inline`.
     */
    public $enableError = true;
    /**
     * @var bool whether to render the label. Default is `true`.
     */
    public $enableLabel = true;


    /**
     * {@inheritdoc}
     */
    public function __construct($config = [])
    {
        $layoutConfig = $this->createLayoutConfig($config);
        $config = ArrayHelper::merge($layoutConfig, $config);
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function render($content = null): string
    {
        if ($content === null) {
            if (!isset($this->parts['{beginWrapper}'])) {
                $options = $this->wrapperOptions;
                $tag = ArrayHelper::remove($options, 'tag', 'div');
                $this->parts['{beginWrapper}'] = Html::beginTag($tag, $options);
                $this->parts['{endWrapper}'] = Html::endTag($tag);
            }
            if ($this->enableLabel === false) {
                $this->parts['{label}'] = '';
                $this->parts['{beginLabel}'] = '';
                $this->parts['{labelTitle}'] = '';
                $this->parts['{endLabel}'] = '';
            } elseif (!isset($this->parts['{beginLabel}'])) {
                $this->renderLabelParts();
            }
            if ($this->enableError === false) {
                $this->parts['{error}'] = '';
            }
            if ($this->inputTemplate) {
                $options = $this->inputOptions;

                if ($this->form->validationStateOn === ActiveForm::VALIDATION_STATE_ON_INPUT) {
                    $this->addErrorClassIfNeeded($options);
                }
                $this->addAriaAttributes($options);

                $input = $this->parts['{input}'] ?? Html::activeTextInput($this->model, $this->attribute, $options);
                $this->parts['{input}'] = strtr($this->inputTemplate, ['{input}' => $input]);
            }
        }

        return parent::render($content);
    }

    /**
     * {@inheritdoc}
     * Enable option `switch` to render as toggle switch.
     * @see https://getbootstrap.com/docs/5.1/forms/checks-radios/#switches
     */
    public function checkbox($options = [], $enclosedByLabel = false)
    {
        $checkOptions = $this->checkOptions;
        $options = ArrayHelper::merge($checkOptions, $options);
        $labelOptions = ArrayHelper::remove($options, 'labelOptions', []);
        $wrapperOptions = ArrayHelper::remove($options, 'wrapperOptions', []);
        Html::removeCssClass($options, 'form-control');
        $this->labelOptions = ArrayHelper::merge($this->labelOptions, $labelOptions);
        $this->wrapperOptions = ArrayHelper::merge($this->wrapperOptions, $wrapperOptions);
        $switch = isset($options['switch']) && $options['switch'];

        if ($switch) {
            $this->addRoleAttributes($options, 'switch');
        }
        if (!isset($options['template'])) {
            if ($switch) {
                $this->template = $enclosedByLabel ? $this->switchEnclosedTemplate : $this->switchTemplate;
            } else {
                $this->template = $enclosedByLabel ? $this->checkEnclosedTemplate : $this->checkTemplate;
            }
        } else {
            $this->template = $options['template'];
        }
        if ($this->form->layout === ActiveForm::LAYOUT_HORIZONTAL) {
            if (!isset($options['template'])) {
                $this->template = ($switch)
                    ? $this->switchHorizontalTemplate
                    : $this->checkHorizontalTemplate;
            }
            Html::removeCssClass($this->labelOptions, $this->horizontalCssClasses['label']);
            Html::addCssClass($this->wrapperOptions, $this->horizontalCssClasses['offset']);
        }
        if ($this->form->layout === ActiveForm::LAYOUT_INLINE) {
            Html::removeCssClass($this->labelOptions, 'visually-hidden');
        }
        Html::removeCssClass($this->labelOptions, 'form-label');
        unset($options['template'], $options['switch']);

        if ($enclosedByLabel) {
            if (isset($options['label'])) {
                $this->parts['{labelTitle}'] = $options['label'];
            }
        }

        return parent::checkbox($options, false);
    }

    /**
     * {@inheritdoc}
     */
    public function radio($options = [], $enclosedByLabel = false)
    {
        $checkOptions = $this->radioOptions;
        $options = ArrayHelper::merge($checkOptions, $options);
        $labelOptions = ArrayHelper::remove($options, 'labelOptions', []);
        $wrapperOptions = ArrayHelper::remove($options, 'wrapperOptions', []);
        Html::removeCssClass($options, 'form-control');
        $this->labelOptions = ArrayHelper::merge($this->labelOptions, $labelOptions);
        $this->wrapperOptions = ArrayHelper::merge($this->wrapperOptions, $wrapperOptions);

        if (!isset($options['template'])) {
            $this->template = $enclosedByLabel ? $this->checkEnclosedTemplate : $this->radioTemplate;
        } else {
            $this->template = $options['template'];
        }
        if ($this->form->layout === ActiveForm::LAYOUT_HORIZONTAL) {
            if (!isset($options['template'])) {
                $this->template = $this->radioHorizontalTemplate;
            }
            Html::removeCssClass($this->labelOptions, $this->horizontalCssClasses['label']);
            Html::addCssClass($this->wrapperOptions, $this->horizontalCssClasses['offset']);
        }
        Html::removeCssClass($this->labelOptions, 'form-label');
        unset($options['template']);

        if ($enclosedByLabel && isset($options['label'])) {
            $this->parts['{labelTitle}'] = $options['label'];
        }

        return parent::radio($options, false);
    }

    /**
     * {@inheritdoc}
     */
    public function checkboxList($items, $options = [])
    {
        if (!isset($options['item'])) {
            $this->template = str_replace("\n{error}", '', $this->template);
            $itemOptions = $options['itemOptions'] ?? [];
            $encode = ArrayHelper::getValue($options, 'encode', true);
            $itemCount = count($items) - 1;
            $error = $this->error()->parts['{error}'];
            $options['item'] = function ($i, $label, $name, $checked, $value) use (
                $itemOptions,
                $encode,
                $itemCount,
                $error
            ) {
                $options = array_merge($this->checkOptions, [
                    'label' => $encode ? Html::encode($label) : $label,
                    'value' => $value,
                ], $itemOptions);
                $wrapperOptions = ArrayHelper::remove($options, 'wrapperOptions', ['class' => ['widget' => 'form-check']]);
                if ($this->inline) {
                    Html::addCssClass($wrapperOptions, ['inline' => 'form-check-inline']);
                }

                $html = Html::beginTag('div', $wrapperOptions) . "\n" .
                    Html::checkbox($name, $checked, $options) . "\n";
                if ($itemCount === $i) {
                    $html .= $error . "\n";
                }
                $html .= Html::endTag('div') . "\n";

                return $html;
            };
        }

        parent::checkboxList($items, $options);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function radioList($items, $options = [])
    {
        if (!isset($options['item'])) {
            $this->template = str_replace("\n{error}", '', $this->template);
            $itemOptions = $options['itemOptions'] ?? [];
            $encode = ArrayHelper::getValue($options, 'encode', true);
            $itemCount = count($items) - 1;
            $error = $this->error()->parts['{error}'];
            $options['item'] = function ($i, $label, $name, $checked, $value) use (
                $itemOptions,
                $encode,
                $itemCount,
                $error
            ) {
                $options = array_merge($this->radioOptions, [
                    'label' => $encode ? Html::encode($label) : $label,
                    'value' => $value,
                ], $itemOptions);
                $wrapperOptions = ArrayHelper::remove($options, 'wrapperOptions', ['class' => ['widget' => 'form-check']]);
                if ($this->inline) {
                    Html::addCssClass($wrapperOptions, ['inline' => 'form-check-inline']);
                }

                $html = Html::beginTag('div', $wrapperOptions) . "\n" .
                    Html::radio($name, $checked, $options) . "\n";
                if ($itemCount === $i) {
                    $html .= $error . "\n";
                }
                $html .= Html::endTag('div') . "\n";

                return $html;
            };
        }

        parent::radioList($items, $options);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function listBox($items, $options = [])
    {
        if ($this->form->layout === ActiveForm::LAYOUT_INLINE) {
            Html::removeCssClass($this->labelOptions, 'visually-hidden');
        }
        Html::addCssClass($options, ['widget' => 'form-select']);

        return parent::listBox($items, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function dropDownList($items, $options = [])
    {
        if ($this->form->layout === ActiveForm::LAYOUT_INLINE) {
            Html::removeCssClass($this->labelOptions, 'visually-hidden');
        }
        Html::addCssClass($options, ['widget' => 'form-select']);

        return parent::dropDownList($items, $options);
    }

    /**
     * Renders Bootstrap static form control.
     * @param array $options the tag options in terms of name-value pairs. These will be rendered as
     * the attributes of the resulting tag. There are also a special options:
     *
     * - encode: bool, whether value should be HTML-encoded or not.
     *
     * @return $this the field object itself
     * @see https://getbootstrap.com/docs/5.1/components/forms/#readonly-plain-text
     */
    public function staticControl(array $options = []): self
    {
        $this->adjustLabelFor($options);
        $this->parts['{input}'] = Html::activeStaticControl($this->model, $this->attribute, $options);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function label($label = null, $options = [])
    {
        if (is_bool($label)) {
            $this->enableLabel = $label;
            if ($label === false && $this->form->layout === ActiveForm::LAYOUT_HORIZONTAL) {
                Html::addCssClass($this->wrapperOptions, $this->horizontalCssClasses['offset']);
            }
        } else {
            $this->enableLabel = true;
            $this->renderLabelParts($label, $options);
            parent::label($label, $options);
        }

        return $this;
    }

    /**
     * @param bool $value whether to render a inline list
     * @return $this the field object itself
     * Make sure you call this method before [[checkboxList()]] or [[radioList()]] to have any effect.
     */
    public function inline($value = true): self
    {
        $this->inline = (bool)$value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function fileInput($options = [])
    {
        Html::addCssClass($options, ['widget' => 'form-control']);

        return parent::fileInput($options);
    }

    /**
     * Renders a range (custom input).
     *
     * @param array $options the tag options in terms of name-value pairs:
     *
     * - 'min': min. value
     * - 'max': max. value
     * - 'step': range step, by default, 1
     *
     * @return $this
     * @see https://getbootstrap.com/docs/5.1/forms/range/
     */
    public function rangeInput(array $options = [])
    {
        Html::addCssClass($options, ['widget' => 'form-range']);

        return $this->input('range', $options);
    }

    /**
     * Renders a color picker (custom input).
     *
     * @param array $options the tag options in terms of name-value pairs
     * @return $this
     * @see https://getbootstrap.com/docs/5.1/forms/form-control/#color
     */
    public function colorInput(array $options = [])
    {
        Html::removeCssClass($options, 'form-control');
        Html::addCssClass($options, ['widget' => 'form-control form-control-color']);

        return $this->input('color', $options);
    }

    /**
     * @param array $instanceConfig the configuration passed to this instance's constructor
     * @return array the layout specific default configuration for this instance
     */
    protected function createLayoutConfig(array $instanceConfig): array
    {
        $config = [
            'hintOptions' => [
                'tag' => 'div',
                'class' => ['form-text', 'text-muted'],
            ],
            'errorOptions' => [
                'tag' => 'div',
                'class' => 'invalid-feedback',
            ],
            'inputOptions' => [
                'class' => 'form-control',
            ],
            'labelOptions' => [
                'class' => ['form-label'],
            ],
        ];

        $layout = $instanceConfig['form']->layout;

        if ($layout === ActiveForm::LAYOUT_HORIZONTAL) {
            $config['template'] = "{label}\n{beginWrapper}\n{input}\n{error}\n{hint}\n{endWrapper}";
            $config['wrapperOptions'] = [];
            $config['labelOptions'] = [];
            $config['options'] = [];
            $cssClasses = [
                'offset' => ['col-sm-10', 'offset-sm-2'],
                'label' => ['col-sm-2', 'col-form-label'],
                'wrapper' => 'col-sm-10',
                'error' => '',
                'hint' => '',
                'field' => 'mb-3 row',
            ];
            if (isset($instanceConfig['horizontalCssClasses'])) {
                $cssClasses = ArrayHelper::merge($cssClasses, $instanceConfig['horizontalCssClasses']);
            }
            $config['horizontalCssClasses'] = $cssClasses;

            Html::addCssClass($config['wrapperOptions'], $cssClasses['wrapper']);
            Html::addCssClass($config['labelOptions'], $cssClasses['label']);
            Html::addCssClass($config['errorOptions'], $cssClasses['error']);
            Html::addCssClass($config['hintOptions'], $cssClasses['hint']);
            Html::addCssClass($config['options'], $cssClasses['field']);
        } elseif ($layout === ActiveForm::LAYOUT_INLINE) {
            $config['inputOptions']['placeholder'] = true;
            $config['enableError'] = false;

            Html::addCssClass($config['labelOptions'], ['screenreader' => 'visually-hidden']);
        } elseif ($layout === ActiveForm::LAYOUT_FLOATING) {
            $config['inputOptions']['placeholder'] = true;
            $config['template'] = "{input}\n{label}\n{error}\n{hint}";
            Html::addCssClass($config['options'], ['layout' => 'form-floating mt-3']);
        }

        return $config;
    }

    /**
     * @param string|null $label the label or null to use model label
     * @param array $options the tag options
     */
    protected function renderLabelParts(?string $label = null, array $options = [])
    {
        $options = array_merge($this->labelOptions, $options);
        if ($label === null) {
            if (isset($options['label'])) {
                $label = $options['label'];
                unset($options['label']);
            } else {
                $attribute = Html::getAttributeName($this->attribute);
                $label = Html::encode($this->model->getAttributeLabel($attribute));
            }
        }
        if (!isset($options['for'])) {
            $options['for'] = Html::getInputId($this->model, $this->attribute);
        }
        $this->parts['{beginLabel}'] = Html::beginTag('label', $options);
        $this->parts['{endLabel}'] = Html::endTag('label');
        if (!isset($this->parts['{labelTitle}'])) {
            $this->parts['{labelTitle}'] = $label;
        }
    }
}
