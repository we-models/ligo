<?php

namespace App\Properties;

use App\Models\BaseModel;
use App\Models\File;
use App\Models\ImageFile;
use Illuminate\Database\Eloquent\Model;

class Prop
{

    private string $name;
    private int $width;
    private string $label;
    private bool $required;
    private array $condition;

    /**
     * @param string $name
     * @param string $label
     * @param array $condition
     * @param int $width
     * @param bool $required
     */
    public function __construct(string $name, string $label, array $condition = [], int $width =6, bool $required = true)
    {
        $this->name = $name;
        $this->label = $label;
        $this->width = $width;
        $this->required = $required;
        $this->condition = $condition;
    }

    private function propertyHeader($sortable):array{
        return [
            'properties' => [
                'width' => $this->width,
                'label' => $this->label,
                'sortable' => $sortable
            ],
        ];
    }

    public function propertyTemplate(array $attributes = [], bool $sortable = true):array{
        return [
            $this->name => array_merge($this->propertyHeader($sortable), $attributes)
        ];
    }


    public function textInput(array $attrib = []):array {
        $attributes = [
            'type' => 'text',
            'minlength' => 1,
            'required' => $this->required,
            'class' => 'form-control',
            'conditions' => $this->condition,
            'isquill' => true
        ];
        $attributes = array_merge($attributes, $attrib);
        return $this->propertyTemplate([
            'attributes' => $attributes
        ]);
    }

    public function colorInput(array $attrib = []):array {
        $attributes = [
            'type' => 'color',
            'required' => $this->required,
            'class' => 'form-control',
            'conditions' => $this->condition
        ];
        $attributes = array_merge($attributes, $attrib);
        return $this->propertyTemplate([
            'attributes' => $attributes
        ]);
    }

    public function intInput(array $attrib = []):array{
        $attributes = [
            'type' => 'number',
            'min' => 0,
            'required' => $this->required,
            'class' => 'form-control',
            'conditions' => $this->condition
        ];
        $attributes = array_merge($attributes, $attrib);

        return $this->propertyTemplate([
            'attributes' => $attributes
        ]);
    }
    public function dateInput():array{
        return $this->propertyTemplate([
            'attributes' => [
                'type' => 'date',
                'required' => $this->required,
                'class' => 'form-control',
                'conditions' => $this->condition
            ]
        ]);
    }

    public function telInput():array{
        return $this->propertyTemplate([
            'attributes' => [
                'type' => 'tel',
                'required' => $this->required,
                'class' => 'form-control',
                'conditions' => $this->condition
            ]
        ]);
    }

    public function textAreaInput():array {
        return $this->propertyTemplate([
            'attributes' => [
                'type' => 'textarea',
            ]
        ]);
    }

    public function largeTextInput():array {
        return $this->propertyTemplate([
            'attributes' => [
                'type' => 'textarea',
                'isquill' => false
            ]
        ]);
    }

    public function selectInput(array $options):array{
        return $this->propertyTemplate([
            'attributes' => [
                'type' => 'select',
                'required' => $this->required,
                'options' => $options,
                'class' => 'form-control',
                'conditions' => $this->condition
            ]
        ]);
    }

    public function variableInput(string $decision): array{
        return $this->propertyTemplate([
            'attributes' => [
                'type' => 'variable',
                'decision' => $decision ,
                'class' => 'form-control',
                'conditions' => $this->condition
            ]
        ]);
    }

    public function booleanInput() : array{
        return $this->propertyTemplate([
            'attributes' => [
                'type' => 'checkbox',
                'class' => 'form-check-input'
            ]
        ]);
    }

    public function optionsInput(string $selector):array{
        return $this->propertyTemplate([
            'attributes' => [
                'type' => 'Array',
                'required' => $this->required,
                'selector' => $selector
            ]
        ]);
    }

    public function objectInput($object, bool $multiple = false, array $depends = [],$attrib = [] ):array{
        $attributes =  [
                'type' => 'object',
                'name' => $this->name ,
                'required' => $this->required,
                'multiple' => $multiple,
                'data' => $object->publicAttributes() ,
                'conditions' => $this->condition,
                'depends' => $depends == [] ? null : $depends,
        ];

        $attributes = array_merge($attributes, $attrib);
        return $this->propertyTemplate([
            'attributes' => $attributes
        ]);
    }

    public function iconInput( $object, bool $multiple = false, array $depends = [] ):array{
        return  $this->propertyTemplate([
            'attributes' => [
                'type' => 'icon',
                'name' => $this->name ,
                'required' => $this->required,
                'multiple' => $multiple,
                'data' => $object->publicAttributes() ,
                'conditions' => $this->condition,
                'depends' => $depends == [] ? null : $depends
            ]
        ]);
    }




    public function imageInput(string $name = "image",  bool $multiple = false ):array{
        return  $this->propertyTemplate([
            'attributes' => [
                'type' => 'image',
                'name' => $name,
                'required' => 'false',
                'multiple' => $multiple,
                'data' => (new ImageFile())->publicAttributes()
            ]
        ], false);
    }

    public function fileInput(string $name = "file",  bool $multiple = false ):array{
        return  $this->propertyTemplate([
            'attributes' => [
                'type' => 'file',
                'name' => $name,
                'required' => 'false',
                'multiple' => $multiple,
                'data' => (new File())->publicAttributes()
            ]
        ], false);
    }


}
