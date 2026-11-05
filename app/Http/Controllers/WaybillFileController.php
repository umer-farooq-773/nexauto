<?php

namespace App\Http\Controllers;

use App\Interfaces\WaybillFileInterface;
use App\Interfaces\WaybillInterface;
use Illuminate\Http\Request;

class WaybillFileController extends BaseController
{
    private $waybillRepo;

    public function __construct(WaybillFileInterface $repo, WaybillInterface $waybillRepo)
    {
        $this->setRepo($repo, 'auth.pages.waybill.files', 'waybill.file');
        $this->waybillRepo = $waybillRepo;
    }

    public function waybillFiles($waybillId)
    {
        $waybill = $this->waybillRepo->show($waybillId);
        if (!$waybill) {
            abort(404);
        }

        $files = $this->_repo->findByWaybill($waybillId);

        return view($this->_directory, compact('waybill', 'files'));
    }

    public function store(Request $request, $waybillId)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'name' => 'nullable|string|max:255',
        ]);

        $waybill = $this->waybillRepo->show($waybillId);
        if (!$waybill) {
            return back()->with('error', 'Waybill not found.');
        }

        $file = $request->file('file');
        $uploaded = $waybill->uploadFile($file, 'waybills/files/');

        $this->_repo->store([
            'waybill_id' => $waybillId,
            'name' => $request->name ?? $file->getClientOriginalName(),
            'path' => $uploaded['path'],
            'status' => 1,
        ]);

        return back()->with('success', 'File uploaded successfully.');
    }

    public function replace(Request $request, $waybillId, $fileId)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $fileRecord = $this->_repo->show($fileId);
        if (!$fileRecord) {
            return back()->with('error', 'File not found.');
        }

        // Delete old file
        $fileRecord->deleteFile($fileRecord->path);

        $file = $request->file('file');
        $waybill = $this->waybillRepo->show($waybillId);
        $uploaded = $waybill->uploadFile($file, 'waybills/files/');

        $this->_repo->update($fileId, [
            'name' => $file->getClientOriginalName(),
            'path' => $uploaded['path'],
        ]);

        return back()->with('success', 'File replaced successfully.');
    }

    public function deleteFile($waybillId, $fileId)
    {
        $this->_repo->removeFile($fileId);
        return back()->with('success', 'File deleted successfully.');
    }
}
