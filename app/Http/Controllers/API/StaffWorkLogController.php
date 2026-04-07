<?php

namespace App\Http\Controllers\API;

use App\Helpers\Database;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * API Public: Riwayat kerja staff (ticket replies + activity log) berdasarkan email.
 * Endpoint: GET /api/staffbikin website
 */
class StaffWorkLogController extends Controller
{
    /**
     * Alias endpoint: GET /api/getStaffBw
     */
    public function getStaffBw(Request $request): JsonResponse
    {
        return $this->getStaffWorkLog($request);
    }

    /**
     * Daftar aktivitas kerja staff: balasan tiket + log aktivitas sistem.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getStaffWorkLog(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $email = trim($validated['email']);
        $startDate = $validated['start_date'] ?? date('Y-m-d 00:00:00');
        $endDate = $validated['end_date'] ?? date('Y-m-d 23:59:59');

        // Pastikan format datetime untuk filter
        if (strlen($startDate) <= 10) {
            $startDate .= ' 00:00:00';
        }
        if (strlen($endDate) <= 10) {
            $endDate .= ' 23:59:59';
        }

        $prefix = Database::prefix();
        $adminsTable = $prefix . 'admins';
        $ticketsTable = $prefix . 'tickets';
        $repliesTable = $prefix . 'ticketreplies';
        $activityTable = $prefix . 'activitylog';
        $feedbackTable = $prefix . 'ticketfeedback';

        // 1. Ambil data admin (email atau nama lengkap)
        $admin = DB::table($adminsTable)
            ->where('email', $email)
            ->orWhereRaw("CONCAT(firstname, ' ', lastname) = ?", [$email])
            ->select('id', 'firstname', 'lastname', 'username', 'email')
            ->first();

        if (!$admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Staff not found',
            ], 404)->header('Access-Control-Allow-Origin', '*');
        }

        $adminId = $admin->id;
        $adminName = trim($admin->firstname . ' ' . $admin->lastname);
        $adminUsername = $admin->username;
        $adminEmail = $admin->email;

        $results = [];
        $ticketRatingMax = 5;

        // 2. Balasan tiket (kerja staff)
        $ticketReplies = DB::table($repliesTable . ' as r')
            ->join($ticketsTable . ' as t', 't.id', '=', 'r.tid')
            ->where(function ($q) use ($adminName, $adminUsername) {
                $q->where('r.admin', $adminName)->orWhere('r.admin', $adminUsername);
            })
            ->whereBetween('r.date', [$startDate, $endDate])
            ->select(
                'r.tid as ticket_internal_id',
                't.tid as id',
                't.tid as no_invoice',
                'r.date as waktu',
                'r.message as description',
                't.status as status'
            )
            ->get();

        foreach ($ticketReplies as $row) {
            $description = $this->convertHtmlBrToNewline($row->description ?? null);
            $description = strip_tags($description);
            $description = mb_strimwidth($description, 0, 200, '...');

            $ticketInternalId = $row->ticket_internal_id ?? null;
            $feedback = $ticketInternalId
                ? DB::table($feedbackTable)->where('ticketid', $ticketInternalId)->where('adminid', $adminId)->select('rating', 'message')->first()
                : null;

            $ratingStr = '-';
            if ($feedback && is_numeric($feedback->rating)) {
                $ratingValue = (float) $feedback->rating;
                $ratingValue = max(0, min($ticketRatingMax, $ratingValue));
                $ratingValueStr = (floor($ratingValue) === $ratingValue) ? (string) (int) $ratingValue : (string) $ratingValue;
                $ratingStr = $ratingValueStr . '/' . $ticketRatingMax;
            }

            $results[] = [
                'id' => $row->id,
                'jenis' => 'bikin website',
                'kategori' => 'tiket',
                'no_invoice' => $row->no_invoice,
                'description' => $description,
                'status' => $row->status,
                'rating' => $ratingStr,
                'email' => $adminEmail,
                'waktu' => date('d-m-Y H:i:s', strtotime($row->waktu)),
            ];
        }

        // 3. Activity log (kecuali yang sudah tercakup di ticket)
        $logs = DB::table($activityTable)
            ->where(function ($q) use ($adminName, $adminUsername) {
                $q->where('user', $adminName)->orWhere('user', $adminUsername);
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->where(function ($q) {
                $q->where('description', 'NOT LIKE', 'Ticket Reply Sent%')
                    ->where('description', 'NOT LIKE', 'Created Ticket%')
                    ->where('description', 'NOT LIKE', 'Changed Ticket Status%');
            })
            ->select(
                'date as waktu',
                DB::raw("CASE
                    WHEN description LIKE '%Invoice%' THEN 'Invoice'
                    WHEN description LIKE '%Order%' THEN 'Order'
                    ELSE 'System'
                END as type"),
                'description',
                DB::raw("'Completed' as status")
            )
            ->get();

        foreach ($logs as $log) {
            $noInvoice = 0;
            $desc = (string) ($log->description ?? '');
            if (preg_match('/Invoice\s+ID\s*:\s*(\d+)/i', $desc, $m)) {
                $noInvoice = (int) $m[1];
            } elseif (preg_match('/Invoice\s*#\s*(\d+)/i', $desc, $m)) {
                $noInvoice = (int) $m[1];
            } elseif (preg_match('/Order\s*#\s*(\d+)/i', $desc, $m)) {
                $noInvoice = (int) $m[1];
            }

            $rawType = strtolower(trim((string) ($log->type ?? '')));
            if ($rawType === 'invoice') {
                $kategori = 'invoice';
            } elseif ($rawType === 'order') {
                $kategori = 'order';
            } else {
                if (stripos($desc, 'Transaction') !== false) {
                    $kategori = 'transaction';
                } elseif (stripos($desc, 'Domain') !== false) {
                    $kategori = 'domain';
                } elseif (stripos($desc, 'Product/Service') !== false) {
                    $kategori = 'service';
                } else {
                    $kategori = 'other';
                }
            }

            $results[] = [
                'id' => $noInvoice,
                'jenis' => 'bikin website',
                'kategori' => $kategori,
                'no_invoice' => $noInvoice,
                'description' => $desc,
                'status' => $log->status,
                'rating' => '-',
                'email' => $adminEmail,
                'waktu' => date('d-m-Y H:i:s', strtotime($log->waktu)),
            ];
        }

        // 4. Urutkan berdasarkan waktu DESC
        usort($results, function ($a, $b) {
            return strtotime(str_replace('/', '-', $b['waktu'])) - strtotime(str_replace('/', '-', $a['waktu']));
        });

        return response()
            ->json([
                'status' => 'success',
                'data' => $results,
            ], 200, [
                'Access-Control-Allow-Origin' => '*',
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Mengubah tag HTML <br> menjadi newline pada string.
     */
    private function convertHtmlBrToNewline(?string $string): string
    {
        $string = $string ?? '';
        return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
    }
}
