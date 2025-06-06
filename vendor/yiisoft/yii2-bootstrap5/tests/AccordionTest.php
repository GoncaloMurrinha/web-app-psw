<?php

namespace yiiunit\extensions\bootstrap5;

use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\bootstrap5\Accordion;
use yii\widgets\ActiveForm;

/**
 * @group bootstrap5
 */
class AccordionTest extends TestCase
{
    public function testRender()
    {
        Accordion::$counter = 0;
        $output = Accordion::widget([
            'items' => [
                [
                    'label' => 'Collapsible Group Item #1',
                    'content' => [
                        'test content1',
                        'test content2'
                    ],
                ],
                [
                    'label' => 'Collapsible Group Item #2',
                    'content' => 'Das ist das Haus vom Nikolaus',
                    'contentOptions' => [
                        'class' => 'testContentOptions'
                    ],
                    'options' => [
                        'class' => 'testClass',
                        'id' => 'testId'
                    ],
                    'footer' => 'Footer'
                ],
                [
                    'label' => '<h1>Collapsible Group Item #3</h1>',
                    'content' => [
                        '<h2>test content1</h2>',
                        '<h2>test content2</h2>'
                    ],
                    'contentOptions' => [
                        'class' => 'testContentOptions2'
                    ],
                    'options' => [
                        'class' => 'testClass2',
                        'id' => 'testId2'
                    ],
                    'encode' => false,
                    'footer' => 'Footer2'
                ],
                [
                    'label' => '<h1>Collapsible Group Item #4</h1>',
                    'content' => [
                        '<h2>test content1</h2>',
                        '<h2>test content2</h2>'
                    ],
                    'contentOptions' => [
                        'class' => 'testContentOptions3'
                    ],
                    'options' => [
                        'class' => 'testClass3',
                        'id' => 'testId3'
                    ],
                    'encode' => true,
                    'footer' => 'Footer3'
                ],
            ]
        ]);

        $this->assertEqualsWithoutLE(<<<HTML
<div id="w0" class="accordion">
<div class="accordion-item"><div id="w0-collapse0-heading" class="accordion-header"><h5 class="mb-0"><button type="button" id="w1" class="accordion-button" data-bs-toggle="collapse" data-bs-target="#w0-collapse0" aria-expanded="true" aria-controls="w0-collapse0">Collapsible Group Item #1</button>
</h5></div>
<div id="w0-collapse0" class="collapse show" aria-labelledby="w0-collapse0-heading" data-bs-parent="#w0">
<ul class="list-group">
<li class="list-group-item">test content1</li>
<li class="list-group-item">test content2</li>
</ul>

</div></div>
<div id="testId" class="testClass accordion-item"><div id="w0-collapse1-heading" class="accordion-header"><h5 class="mb-0"><button type="button" id="w2" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#w0-collapse1" aria-expanded="false" aria-controls="w0-collapse1">Collapsible Group Item #2</button>
</h5></div>
<div id="w0-collapse1" class="testContentOptions collapse" aria-labelledby="w0-collapse1-heading" data-bs-parent="#w0">
<div class="accordion-body">Das ist das Haus vom Nikolaus</div>

<div class="accordion-footer">Footer</div>
</div></div>
<div id="testId2" class="testClass2 accordion-item"><div id="w0-collapse2-heading" class="accordion-header"><h5 class="mb-0"><button type="button" id="w3" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#w0-collapse2" aria-expanded="false" aria-controls="w0-collapse2"><h1>Collapsible Group Item #3</h1></button>
</h5></div>
<div id="w0-collapse2" class="testContentOptions2 collapse" aria-labelledby="w0-collapse2-heading" data-bs-parent="#w0">
<ul class="list-group">
<li class="list-group-item"><h2>test content1</h2></li>
<li class="list-group-item"><h2>test content2</h2></li>
</ul>

<div class="accordion-footer">Footer2</div>
</div></div>
<div id="testId3" class="testClass3 accordion-item"><div id="w0-collapse3-heading" class="accordion-header"><h5 class="mb-0"><button type="button" id="w4" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#w0-collapse3" aria-expanded="false" aria-controls="w0-collapse3">&lt;h1&gt;Collapsible Group Item #4&lt;/h1&gt;</button>
</h5></div>
<div id="w0-collapse3" class="testContentOptions3 collapse" aria-labelledby="w0-collapse3-heading" data-bs-parent="#w0">
<ul class="list-group">
<li class="list-group-item"><h2>test content1</h2></li>
<li class="list-group-item"><h2>test content2</h2></li>
</ul>

<div class="accordion-footer">Footer3</div>
</div></div>
</div>

HTML
            , $output);
    }

    public function testLabelKeys()
    {
        ob_start();
        $form = ActiveForm::begin(['action' => '/something']);
        ActiveForm::end();
        ob_end_clean();

        Accordion::$counter = 0;
        $output = Accordion::widget([
            'items' => [
                'Item1' => 'Content1',
                'Item2' => [
                    'content' => 'Content2',
                ],
                [
                    'label' => 'Item3',
                    'content' => 'Content3',
                ],
                'FormField' => $form->field(new DynamicModel(['test']), 'test', ['template' => '{input}']),
            ]
        ]);

        $this->assertEqualsWithoutLE(<<<HTML
<div id="w0" class="accordion">
<div class="accordion-item"><div id="w0-collapse0-heading" class="accordion-header"><h5 class="mb-0"><button type="button" id="w1" class="accordion-button" data-bs-toggle="collapse" data-bs-target="#w0-collapse0" aria-expanded="true" aria-controls="w0-collapse0">Item1</button>
</h5></div>
<div id="w0-collapse0" class="collapse show" aria-labelledby="w0-collapse0-heading" data-bs-parent="#w0">
<div class="accordion-body">Content1</div>

</div></div>
<div class="accordion-item"><div id="w0-collapse1-heading" class="accordion-header"><h5 class="mb-0"><button type="button" id="w2" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#w0-collapse1" aria-expanded="false" aria-controls="w0-collapse1">Item2</button>
</h5></div>
<div id="w0-collapse1" class="collapse" aria-labelledby="w0-collapse1-heading" data-bs-parent="#w0">
<div class="accordion-body">Content2</div>

</div></div>
<div class="accordion-item"><div id="w0-collapse2-heading" class="accordion-header"><h5 class="mb-0"><button type="button" id="w3" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#w0-collapse2" aria-expanded="false" aria-controls="w0-collapse2">Item3</button>
</h5></div>
<div id="w0-collapse2" class="collapse" aria-labelledby="w0-collapse2-heading" data-bs-parent="#w0">
<div class="accordion-body">Content3</div>

</div></div>
<div class="accordion-item"><div id="w0-collapse3-heading" class="accordion-header"><h5 class="mb-0"><button type="button" id="w4" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#w0-collapse3" aria-expanded="false" aria-controls="w0-collapse3">FormField</button>
</h5></div>
<div id="w0-collapse3" class="collapse" aria-labelledby="w0-collapse3-heading" data-bs-parent="#w0">
<div class="accordion-body"><div class="form-group field-dynamicmodel-test">
<input type="text" id="dynamicmodel-test" class="form-control" name="DynamicModel[test]">
</div></div>

</div></div>
</div>

HTML
            , $output);
    }

    public function testExpandOptions()
    {
        Accordion::$counter = 0;
        $output = Accordion::widget([
            'items' => [
                'Item1' => 'Content1',
                'Item2' => [
                    'content' => 'Content2',
                    'expand' => true,
                ],
            ]
        ]);

        $this->assertEqualsWithoutLE(<<<HTML
<div id="w0" class="accordion">
<div class="accordion-item"><div id="w0-collapse0-heading" class="accordion-header"><h5 class="mb-0"><button type="button" id="w1" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#w0-collapse0" aria-expanded="false" aria-controls="w0-collapse0">Item1</button>
</h5></div>
<div id="w0-collapse0" class="collapse" aria-labelledby="w0-collapse0-heading" data-bs-parent="#w0">
<div class="accordion-body">Content1</div>

</div></div>
<div class="accordion-item"><div id="w0-collapse1-heading" class="accordion-header"><h5 class="mb-0"><button type="button" id="w2" class="accordion-button" data-bs-toggle="collapse" data-bs-target="#w0-collapse1" aria-expanded="true" aria-controls="w0-collapse1">Item2</button>
</h5></div>
<div id="w0-collapse1" class="collapse show" aria-labelledby="w0-collapse1-heading" data-bs-parent="#w0">
<div class="accordion-body">Content2</div>

</div></div>
</div>

HTML
            , $output);
    }

    public function invalidItemsProvider()
    {
        return [
            [['content']], // only content without label key
            [[[]]], // only content array without label
            [[['content' => 'test']]], // only content array without label
        ];
    }

    /**
     * @dataProvider invalidItemsProvider
     */
    public function testMissingLabel($items)
    {
        $this->expectException(\yii\base\InvalidConfigException::class);
        Accordion::widget([
            'items' => $items,
        ]);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/8357
     */
    public function testRenderObject()
    {
        $template = ['template' => '{input}'];
        ob_start();
        $form = ActiveForm::begin(['action' => '/something']);
        ActiveForm::end();
        ob_end_clean();
        $model = new data\Singer;

        Accordion::$counter = 0;
        $output = Accordion::widget([
            'items' => [
                [
                    'label' => 'Collapsible Group Item #1',
                    'content' => $form->field($model, 'firstName', $template)
                ],
            ]
        ]);

        $this->assertEqualsWithoutLE(<<<HTML
<div id="w0" class="accordion">
<div class="accordion-item"><div id="w0-collapse0-heading" class="accordion-header"><h5 class="mb-0"><button type="button" id="w1" class="accordion-button" data-bs-toggle="collapse" data-bs-target="#w0-collapse0" aria-expanded="true" aria-controls="w0-collapse0">Collapsible Group Item #1</button>
</h5></div>
<div id="w0-collapse0" class="collapse show" aria-labelledby="w0-collapse0-heading" data-bs-parent="#w0">
<div class="accordion-body"><div class="form-group field-singer-firstname">
<input type="text" id="singer-firstname" class="form-control" name="Singer[firstName]">
</div></div>

</div></div>
</div>

HTML
            , $output);
    }

    public function testAutoCloseItems()
    {
        $items = [
            [
                'label' => 'Item 1',
                'content' => 'Content 1',
            ],
            [
                'label' => 'Item 2',
                'content' => 'Content 2',
            ],
        ];

        $output = Accordion::widget([
            'items' => $items
        ]);
        $this->assertStringContainsString('data-bs-parent="', $output);
        $output = Accordion::widget([
            'autoCloseItems' => false,
            'items' => $items
        ]);
        $this->assertStringNotContainsString('data-bs-parent="', $output);
    }

    /**
     */
    public function testItemToggleTag()
    {
        $items = [
            [
                'label' => 'Item 1',
                'content' => 'Content 1',
            ],
            [
                'label' => 'Item 2',
                'content' => 'Content 2',
            ],
        ];

        Accordion::$counter = 0;

        $output = Accordion::widget([
            'items' => $items,
            'itemToggleOptions' => [
                'tag' => 'a',
                'class' => 'custom-toggle',
            ],
        ]);
        $this->assertStringContainsString('<h5 class="mb-0"><a type="button" class="custom-toggle" href="#w0-collapse0" ', $output);
        $this->assertStringNotContainsString('<button', $output);

        $output = Accordion::widget([
            'items' => $items,
            'itemToggleOptions' => [
                'tag' => 'a',
                'class' => ['widget' => 'custom-toggle'],
            ],
        ]);
        $this->assertStringContainsString('<h5 class="mb-0"><a type="button" class="custom-toggle" href="#w1-collapse0" ', $output);
        $this->assertStringNotContainsString('collapse-toggle', $output);
    }
}
