<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Validation\Rules\In;
use Livewire\Component;

class PdsChildrenForm extends Component
{
    
    public $children = [
        ['name' => '', 'dob' => '']
    ];

    public function addEmptyChild()
    {   
        Info('Adding an empty child');
        
        $this->children[] = ['name' => '', 'dob' => ''];
    }

    private function normalizeDobForInput($value): string
    {
        $text = is_string($value) ? trim($value) : '';
        if ($text === '') {
            return '';
        }

        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $text)) {
                return Carbon::createFromFormat('Y-m-d', $text)->format('Y-m-d');
            }

            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $text)) {
                return Carbon::createFromFormat('d-m-Y', $text)->format('Y-m-d');
            }

            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $text)) {
                return Carbon::createFromFormat('d/m/Y', $text)->format('Y-m-d');
            }

            return Carbon::parse($text)->format('Y-m-d');
        } catch (\Throwable $e) {
            return '';
        }
    }
/*
    public function mount($children) {
        if (empty($children)) {
            $this->addEmptyChild();
        }
        else {
            $this->children = $children;
        }
    }*/

    public function mount($children)
    {   
        info('Mounting PdsChildrenForm with children: ', $children);
        if (!is_array($children) || empty($children)) {
            $this->addEmptyChild();
        } else {
            $this->children = collect($children)->map(function ($child) {
                return [
                    'name' => $child['name'] ?? '',
                    'dob' => $this->normalizeDobForInput($child['dob'] ?? ''),
                ];
            })->toArray();
        }
    }

    public function render()
    {
        return view('livewire.pds-children-form');
    }

    public function removeChild($index)
{   
    info('Removing child at index: ' . $index);
    unset($this->children[$index]);
    $this->children = array_values($this->children);


}
}
