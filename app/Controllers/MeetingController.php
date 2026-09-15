<?php

namespace App\Controllers;

use App\Models\EvidenceCategoryModel;
use App\Models\MeetingModel;
use App\Models\ProjectEvidenceModel;
use App\Models\ProjectModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Dompdf\Dompdf;
use Dompdf\Options;

class MeetingController extends BaseController
{
    public function index()
    {
        $request = service('request');

        $filters = [
            'project_id' => $request->getGet('project_id'),
            'date_from'  => $request->getGet('date_from'),
            'date_to'    => $request->getGet('date_to'),
            'keyword'    => $request->getGet('keyword'),
        ];

        $meetingModel = new MeetingModel();
        $meetings     = $meetingModel->getFiltered($filters, 12);

        return view('meetings/index', [
            'title'    => 'Notulensi Meeting',
            'meetings' => $meetings,
            'pager'    => $meetingModel->pager,
            'filters'  => $filters,
            'projects' => (new ProjectModel())->select('id, project_code, title')->findAll(),
        ]);
    }

    public function create()
    {
        return view('meetings/form', [
            'title'    => 'Notulensi Baru',
            'row'      => [],
            'isEdit'   => false,
            'projects' => (new ProjectModel())->select('id, project_code, title')->findAll(),
        ]);
    }

    public function store()
    {
        $request      = service('request');
        $meetingModel = new MeetingModel();

        $data = [
            'project_id'       => $request->getPost('project_id') ?: null,
            'title'            => $request->getPost('title'),
            'meeting_date'     => $request->getPost('meeting_date'),
            'problem'          => clean_comment_html($request->getPost('problem')),
            'expected_outcome' => clean_comment_html($request->getPost('expected_outcome')),
            'notes'            => clean_comment_html($request->getPost('notes')),
            'created_by'       => auth()->id(),
        ];

        if (! $meetingModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $meetingModel->errors());
        }

        session()->setFlashdata('success', 'Notulensi berhasil disimpan.');

        return redirect()->to('/meetings/' . $meetingModel->getInsertID());
    }

    public function edit(int $id)
    {
        $meetingModel = new MeetingModel();
        $row          = $meetingModel->find($id);

        if (! $row) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('meetings/form', [
            'title'    => 'Edit Notulensi',
            'row'      => $row,
            'isEdit'   => true,
            'projects' => (new ProjectModel())->select('id, project_code, title')->findAll(),
        ]);
    }

    public function update(int $id)
    {
        $request      = service('request');
        $meetingModel = new MeetingModel();

        $data = [
            'project_id'       => $request->getPost('project_id') ?: null,
            'title'            => $request->getPost('title'),
            'meeting_date'     => $request->getPost('meeting_date'),
            'problem'          => clean_comment_html($request->getPost('problem')),
            'expected_outcome' => clean_comment_html($request->getPost('expected_outcome')),
            'notes'            => clean_comment_html($request->getPost('notes')),
        ];

        if (! $meetingModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $meetingModel->errors());
        }

        session()->setFlashdata('success', 'Notulensi berhasil diperbarui.');

        return redirect()->to('/meetings/' . $id);
    }

    public function delete(int $id)
    {
        (new MeetingModel())->delete($id);

        session()->setFlashdata('success', 'Notulensi berhasil dihapus.');

        return redirect()->to('/meetings');
    }

    public function detail(int $id)
    {
        $meetingModel = new MeetingModel();
        $meeting      = $meetingModel->withDetails()->where('meetings.id', $id)->first();

        if (! $meeting) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('meetings/detail', [
            'title'      => $meeting['title'],
            'meeting'    => $meeting,
            'evidences'  => (new ProjectEvidenceModel())->getByMeeting($id),
            'categories' => (new EvidenceCategoryModel())->findAll(),
        ]);
    }

    public function storeAttachment(int $id)
    {
        $meetingModel = new MeetingModel();
        $meeting      = $meetingModel->find($id);

        if (! $meeting) {
            throw PageNotFoundException::forPageNotFound();
        }

        $request = service('request');
        $file    = $request->getFile('attachment');

        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            session()->setFlashdata('error', 'File tidak valid.');
            return redirect()->to('/meetings/' . $id);
        }

        if (! in_array($file->getClientMimeType(), allowed_attachment_mimes(), true)) {
            session()->setFlashdata('error', 'Tipe file tidak didukung.');
            return redirect()->to('/meetings/' . $id);
        }

        if ($file->getSize() > 10 * 1024 * 1024) {
            session()->setFlashdata('error', 'Ukuran file maksimal 10MB.');
            return redirect()->to('/meetings/' . $id);
        }

        $newName    = $file->getRandomName();
        $uploadPath = FCPATH . 'uploads/evidence';

        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $file->move($uploadPath, $newName);

        (new ProjectEvidenceModel())->insert([
            'meeting_id'    => $id,
            'category_id'   => $request->getPost('category_id'),
            'file_name'     => $newName,
            'original_name' => $file->getClientName(),
            'mime_type'     => $file->getClientMimeType(),
            'file_size'     => $file->getSize(),
            'caption'       => $request->getPost('caption'),
            'uploaded_by'   => auth()->id(),
        ]);

        session()->setFlashdata('success', 'Lampiran berhasil diupload.');

        return redirect()->to('/meetings/' . $id);
    }

    public function exportPdf(int $id)
    {
        $meetingModel = new MeetingModel();
        $meeting      = $meetingModel->withDetails()->where('meetings.id', $id)->first();

        if (! $meeting) {
            throw PageNotFoundException::forPageNotFound();
        }

        $evidences = (new ProjectEvidenceModel())->getByMeeting($id);

        $html = view('meetings/pdf_report', [
            'meeting'     => $meeting,
            'evidences'   => $evidences,
            'generatedAt' => date('d M Y H:i'),
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', realpath(FCPATH)); // ini kuncinya

        $dompdf = new Dompdf($options); // bukan: new Dompdf()
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html);
        $dompdf->render();

        $filename = 'Notulensi-' . preg_replace('/[^A-Za-z0-9\-]/', '-', $meeting['title']) . '.pdf';

        return $this->response
            ->setContentType('application/pdf')
            ->setBody($dompdf->output())
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"');
    }
}
