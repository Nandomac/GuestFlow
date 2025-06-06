<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Models\WorkcenterDowntime;
use App\Models\WorkcenterStructure;
use Illuminate\Support\Facades\Auth;
use App\Models\WorkcenterDowntimeEmail;
use Illuminate\Support\Facades\Validator;

class WorkcenterDowntimeEmailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id, $downtime_cause_id)
    {

        $workcenterDowntime = WorkcenterDowntime::where('downtime_cause_id', $downtime_cause_id)
            ->where('workcenter_structure_id', $id)
            ->first();

        if (!is_null($workcenterDowntime)) {
            $listMails = WorkcenterDowntimeEmail::where('workcenter_downtime_id', $workcenterDowntime->id)->get();

            return view('workcenter.worcenter_downtime_mail', [
                'listMails' => $listMails,
                'workcenterDowntime' => $workcenterDowntime
            ]);
        } else {
            $returnData = array(
                'status' => 'error',
                'message' => 'An error occurred "Workcenter Downtime not found".'
            );
            return Response::json($returnData, 404);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ], [
            'email.required' => 'Email is mandatory.',
            'email.email' => 'The email format is invalid.',
        ], [
            'email' => 'Email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error.',
                'errors' => $validator->errors()
            ], 404);
        }


        try {

            $workcenterDowntime = WorkcenterDowntime::findOrFail($request->workcenter_downtime_id);

            if ($request->id != null) {

                $workcenterDonwtimeEmail = WorkcenterDowntimeEmail::findOrFail($request->id);
                $workcenterDonwtimeEmail->email = $request->email;
                $workcenterDonwtimeEmail->user_update_id = Auth::id();
                $workcenterDonwtimeEmail->save();

            } else {

                WorkcenterDowntimeEmail::create([
                    'workcenter_downtime_id' => $workcenterDowntime->id,
                    'email' => $request->email,
                    'user_create_id' => Auth::id(),
                ]);

            }

            $workcenter_root = WorkcenterStructure::with('childrenRecursive', 'downtimes')->find($workcenterDowntime->workcenter_structure_id);
            $workcenters = collect([$workcenter_root])->merge($this->flattenChildren($workcenter_root->childrenRecursive));

            if ($request->emailOld != ''){
                $searchMail = $request->emailOld;
            } else {
                $searchMail = $request->email;
            }


            foreach ($workcenters as $workcenter) {

                if ($workcenter->id != $workcenterDowntime->workcenter_structure_id) {

                    $foundDowntime = $workcenter->downtimes->firstWhere('downtime_cause_id', $workcenterDowntime->downtime_cause_id);

                    if ($foundDowntime) {

                        $workcenterDonwtimeEmailList = WorkcenterDowntimeEmail::where('email', $searchMail)->where('workcenter_downtime_id', $foundDowntime->id)->get();

                        if ($workcenterDonwtimeEmailList->isNotEmpty()) {

                            foreach ($workcenterDonwtimeEmailList as $mail) {

                                if ($mail->id != $request->id) {

                                    $mail->email = $request->email;
                                    $mail->user_update_id = Auth::id();
                                    $mail->save();
                                }

                            }

                        } else {

                            WorkcenterDowntimeEmail::create([
                                'workcenter_downtime_id' => $foundDowntime->id,
                                'email' => $request->email,
                                'user_create_id' => Auth::id(),
                            ]);

                        }

                    }

                }

            }

            $returnData = array(
                'status' => 'success',
                'message' => 'Success',
            );
            return response()->json($returnData, 200);

        } catch (\Throwable $e) {
            $returnData = array(
                'status' => 'error',
                'message' => $e->getMessage(),
            );
            return response()->json($returnData, 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $workcenterDowntimeEmail = WorkcenterDowntimeEmail::findOrFail($id);
            $workcenterDowntime = WorkcenterDowntime::findOrFail($workcenterDowntimeEmail->workcenter_downtime_id);

            $searchMail = $workcenterDowntimeEmail->email;

            $workcenterDowntimeEmail->user_update_id = Auth::id();
            $workcenterDowntimeEmail->save();
            $workcenterDowntimeEmail->delete();


            $workcenter_root = WorkcenterStructure::with('childrenRecursive', 'downtimes')->find($workcenterDowntime->workcenter_structure_id);
            $workcenters = collect([$workcenter_root])->merge($this->flattenChildren($workcenter_root->childrenRecursive));

            foreach ($workcenters as $workcenter) {

                if ($workcenter->id != $workcenterDowntime->workcenter_structure_id) {

                    $foundDowntime = $workcenter->downtimes->firstWhere('downtime_cause_id', $workcenterDowntime->downtime_cause_id);

                    if ($foundDowntime) {

                        $workcenterDonwtimeEmailList = WorkcenterDowntimeEmail::where('email', $searchMail)->where('workcenter_downtime_id', $foundDowntime->id)->get();

                        if ($workcenterDonwtimeEmailList->isNotEmpty()) {

                            foreach ($workcenterDonwtimeEmailList as $mail) {

                                $mail->user_update_id = Auth::id();
                                $mail->save();
                                $mail->delete();

                            }

                        }

                    }

                }

            }

            return response()->json([
                'success' => true,
                'message' => 'Workcenter downtime email removed successfully.'
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error removing email. Details: ' . $e->getMessage()
            ], 404);
        }
    }

    private function flattenChildren($children)
    {
        $all = collect();

        foreach ($children as $child) {
            $all->push($child);
            if ($child->childrenRecursive->isNotEmpty()) {
                $all = $all->merge($this->flattenChildren($child->childrenRecursive));
            }
        }

        return $all;
    }
}
