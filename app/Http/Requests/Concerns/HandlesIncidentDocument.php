<?php

namespace App\Http\Requests\Concerns;

trait HandlesIncidentDocument
{
    protected function incidentDocumentRules(): array
    {
        return [
            'nullable',
            'file',
            'mimes:pdf,jpg,jpeg,png,doc,docx',
            'mimetypes:application/pdf,image/jpeg,image/png,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'document.mimes' => 'The document must be a PDF, JPG, JPEG, PNG, DOC, or DOCX file.',
            'document.mimetypes' => 'The selected document format is not supported.',
            'document.max' => 'The document may not be greater than 10 MB.',
        ];
    }
}
