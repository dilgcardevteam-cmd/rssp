<?php

namespace App\Livewire;

use Livewire\Component;

class PdsEducationForm extends Component
{

    public $education_type;
    public $education_data = [];
    public $education_type_meta = [];

    public function addRow() {
        $this->education_data[] = [
            'from' => '',
            'to' => '',
            'school' => '',
            'basic' => '',
            'earned' => '',
            'year_graduated' => '',
            'academic_honors' => '',
        ];
    }

    public function removeRow($index) {
        unset($this->education_data[$index]);
        $this->education_data = array_values($this->education_data);
    }
    

    public function mount($education_type, $education_data) {
        $this->education_type = $education_type;
        if (empty($education_data)) {
            $this->addRow();
        }
        else {
            $this->education_data = $education_data;
        }
        

        switch ($this->education_type) {
            case 'vocational':
                $this->education_type_meta = [
                    "title" => "Vocational / Trade Course",
                ];
                break;
            case 'college':
                $this->education_type_meta = [
                    "title" => "College",
                ];
                break;
            case 'grad':
                $this->education_type_meta = [
                    "title" => "Graduate Studies",
                ];
                break;
        }
    }

    public function render()
    {

        
        return view('livewire.pds-education-form');
    }
}
