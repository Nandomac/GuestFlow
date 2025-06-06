<?php

namespace App\Http\Controllers;

use App\Models\Characteristic;
use App\Models\CharacteristicGroup;
use \PDO;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\WorkcenterFile;
use App\Models\WorkcenterDowntime;
use Illuminate\Support\Facades\DB;
use App\Models\WorkcenterStructure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Exceptions\WorkcenterTemplateException;

use App\Services\OracleService;

class WorkcenterStructureController extends Controller
{
    public function showWorkcenterTree()
    {
        $structure_tree = WorkcenterStructure::whereNull('structure_parent_id')
            ->with('childrenRecursive')
            ->get();

        return view('workcenter.workcenter', [
            'tree' => $structure_tree,
        ]);
    }

    public function syncWorkcenters(OracleService $oracleService)
    {
        try {
            $workcenterToSync = $oracleService->getWorkcenterToSync();
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }

        try {
            $workcenters = collect($workcenterToSync);
            $contract = null;
            $departmentNo = null;
            $departamentId = null;
            $productLineNo = null;
            $productLineId = null;
            $workcenterNo = null;
            $workcenterId = null;

            foreach ($workcenters as $key => $workcenter) {

                if($workcenter['CONTRACT'] != $contract){
                    $contract = $workcenter['CONTRACT'];
                }

                if($workcenter['DEPARTMENT_NO'] != $departmentNo) {

                    $departamentId = $this->insertUpdateStructure([
                        'structure_code'      => $workcenter['DEPARTMENT_NO'],
                        'structure_name'      => $workcenter['DEPARTMENT_NAME'],
                        'structure_type'      => 'D',
                        'structure_contract'  => $contract,
                        'structure_parent_id' => null
                    ]);

                }

                if($workcenter['PRODUCTION_LINE'] != $productLineNo) {

                    $productLineId = $this->insertUpdateStructure([
                        'structure_code'      => $workcenter['PRODUCTION_LINE'],
                        'structure_name'      => $workcenter['PROD_LINE_NAME'],
                        'structure_type'      => 'PL',
                        'structure_contract'  => $contract,
                        'structure_parent_id' => $departamentId
                    ]);

                }

                if($workcenter['WORK_CENTER_NO'] != $workcenterNo) {

                    $workcenterId = $this->insertUpdateStructure([
                        'structure_code'      => $workcenter['WORK_CENTER_NO'],
                        'structure_name'      => $workcenter['DESCRIPTION'],
                        'structure_type'      => 'WC',
                        'structure_contract'  => $contract,
                        'structure_parent_id' => $productLineId,
                        'isCritical'         => $workcenter['CRITICAL_WORKCENTER'] === 'Y' ? 1 : 0,
                    ]);

                }

            }

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully synchronized Workcenters.'
            ],200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }

    }

    private function insertUpdateStructure(array $data): int
    {
        $existing = DB::table('workcenter_structures')
            ->where('structure_code', $data['structure_code'])
            ->where('structure_contract', $data['structure_contract'])
            ->first();

        if ($existing) {
            $needsUpdate = false;

            if ($data['structure_name'] !== $existing->structure_name) {
                $needsUpdate = true;
            }

            if (
                array_key_exists('structure_parent_id', $data) &&
                $data['structure_parent_id'] != $existing->structure_parent_id
            ) {
                $needsUpdate = true;
            }

            if (
                array_key_exists('isCritical', $data) &&
                $data['isCritical'] != $existing->isCritical
            ) {
                $needsUpdate = true;
            }

            if ($needsUpdate) {
                DB::table('workcenter_structures')
                    ->where('id', $existing->id)
                    ->update([
                        'structure_name'      => $data['structure_name'],
                        'structure_parent_id' => $data['structure_parent_id'] ?? null,
                        'isCritical'          => $data['isCritical'] ?? 0,
                        'updated_at'          => now()
                    ]);
            }

            return $existing->id;
        }

        $data['created_at'] = now();

        return DB::table('workcenter_structures')->insertGetId($data);
    }

    public function syncGlobalDowntimes(OracleService $oracleService)
    {
        try {
            $downtimes = $oracleService->getDowntimeCauses();
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }

        $workcenters = WorkcenterStructure::all();

        foreach ($downtimes as $downtime) {
            if (strtoupper($downtime['GLOBAL_DOWNTIME']) === 'YES') {

                foreach ($workcenters as $workcenter) {
                    WorkcenterDowntime::withTrashed()->firstOrCreate(
                        [
                            'downtime_cause_id' => $downtime['DOWNTIME_CAUSE_ID'],
                            'workcenter_structure_id' => $workcenter->id
                        ],
                        [
                            'user_create_id' => Auth::id()
                        ]
                    );
                }

            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully synchronized Global Downtimes.'
        ],200);
    }

    public function getWorkcenterDetails($id, OracleService $oracleService)
    {
        try {
            $downtimes = $oracleService->getDowntimeCauses();
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }

        $array = [];
        $workcenter = WorkcenterStructure::find($id);

        if ($workcenter->structure_type == 'WC') {
            $typename = 'Workcenter';
        } elseif ($workcenter->structure_type == 'PL') {
            $typename = 'Production Line';
        } elseif ($workcenter->structure_type == 'D') {
            $typename = 'Department';
        } else {
            $typename = 'Unknown';
        }

        $array[0] = 'Editing ' . $typename . ': ' . $workcenter->structure_code . ' - ' .  $workcenter->structure_name;

        $array[1] = '';

        $array[2] = view('workcenter.workcenter_downtime', [
            'downtimes' => $downtimes,
            'workcenter' => $workcenter,
        ])->render();


        //-----------Aba Info--------------------
        try {
            $locations = $oracleService->getWorkcenterLocations($workcenter->structure_code);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }

        $array[1] = view('workcenter.workcenter_info', [
            'locations' => $locations,
            'workcenter' => $workcenter,
            'file_path' =>  $workcenter->workinstructionFile->path ?? null,
        ])->render();

        //-----------Aba Template Validation--------------------

        $characteristics = Characteristic::whereDoesntHave('workcenters', function ($query) use ($id) {
            $query->where('workcenter_structures.id', "=", $id);
        })->get();

        $characteristics_validation = Characteristic::whereDoesntHave('workcenters', function ($query) use ($id) {
            $query->where('workcenter_structures.id', "=", $id);
        })->where("type", "=", "validation")->get();

        $characteristics_setup= Characteristic::whereDoesntHave('workcenters', function ($query) use ($id) {
            $query->where('workcenter_structures.id', "=", $id);
        })->where("type", "=", "setup")->get();

        $array[3] = view('workcenter.workcenter_template_validation', [
            'characteristics' => $characteristics,
            'characteristics_validation' => $characteristics_validation,
            'characteristics_setup' => $characteristics_setup,
            'workcenter' => $workcenter,
        ])->render();

        //-----------Aba Template Setup--------------------

        $characteristics_group = DB::table('workcenter_template')
        ->join('characteristics', 'workcenter_template.characteristic_id', '=', 'characteristics.id')
        ->join('characteristic_groups', 'workcenter_template.characteristic_group_id', '=', 'characteristic_groups.id')
        ->where('workcenter_template.workcenter_structure_id', $id)
        ->where('characteristics.type', 'setup')
        ->whereNull('workcenter_template.deleted_at')
        ->orderBy('workcenter_template.characteristic_group_order')
        ->select(
            'characteristics.id',
            'characteristics.description',
            'characteristics.type',
            'workcenter_template.cols',
            'workcenter_template.order',
            'workcenter_template.characteristic_group_order',
            'characteristic_groups.id as group_id',
            'characteristic_groups.name as group_name',
        )
        ->orderBy('workcenter_template.characteristic_group_order')
        ->get();

        $array[4] = view('workcenter.workcenter_template_setup', [
            'characteristics_group' => $characteristics_group,
            'characteristics' => $characteristics,
            'characteristics_validation' => $characteristics_validation,
            'characteristics_setup' => $characteristics_setup,
            'workcenter' => $workcenter,
        ])->render();
        return $array;
    }

    public function updateDowntimeStatus(Request $request)
    {
        $downtimeId = $request->input('downtimeId');
        $workcenterId = $request->input('workcenterId');
        $isChecked = $request->input('isChecked');

        try {
            $rootStructure = WorkcenterStructure::with('childrenRecursive')->findOrFail($workcenterId);

            $structures = collect([$rootStructure])->merge($this->flattenChildren($rootStructure->childrenRecursive));

            foreach ($structures as $structure) {
                $workcenterDowntime = WorkcenterDowntime::withTrashed()
                    ->where('downtime_cause_id', $downtimeId)
                    ->where('workcenter_structure_id', $structure->id)
                    ->first();

                if ($isChecked === "true") {
                    if ($workcenterDowntime) {
                        if ($workcenterDowntime->trashed()) {
                            $workcenterDowntime->restore();
                            $workcenterDowntime->user_update_id = Auth::id();
                            $workcenterDowntime->save();
                        }
                    } else {
                        WorkcenterDowntime::create([
                            'downtime_cause_id' => $downtimeId,
                            'workcenter_structure_id' => $structure->id,
                            'user_create_id' => Auth::id(),
                        ]);
                    }
                } else {
                    if ($workcenterDowntime) {
                        $workcenterDowntime->user_update_id = Auth::id();
                        $workcenterDowntime->save();
                        $workcenterDowntime->delete();
                    }
                }
            }

            $returnData = array(
                'status' => 'success',
                'message' => 'Success'
            );
            return response()->json($returnData, 200);
        } catch (\Throwable $e) {
            $returnData = array(
                'status' => 'error',
                'message' => 'Error (toggleDowntime): ' . $e->getMessage()
            );
            return response()->json($returnData, 404);
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

    public function saveWorkcenterInfo(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'file_input' => 'nullable|mimes:pdf|max:10240',
            'structure_code' => 'required|string',
            'multibatch' => 'required|in:yes,no'
        ], [
            'file_input.file' => 'The uploaded file is not valid.',
            'file_input.mimes' => 'Only PDF files are allowed.',
            'file_input.max' => 'The file cannot exceed 10 MB.',
            'structure_code.required' => 'Structure code is mandatory.',
            'structure_code.string' => 'The structure code must be a string',
            'required.required' => 'Multi-Batch is mandatory.',
        ], [
            'file_input' => 'Work Instruction PDF File',
            'structure_code' => 'Workcenter id',
            'multibatch' => 'Multi-Batch'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error.',
                'errors' => $validator->errors()
            ], 404);
        }

        $file = $request->file('file_input');
        $info_input_all = $request->input('info_input_all');
        $workcenter_structure_id = $request->input('workcenter_structure_id');
        $structureCode = $request->input('structure_code');
        $multibatch = $request->input('multibatch');

        if ($file) {
            $realMime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath());

            if ($realMime !== 'application/pdf') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The file content is not a valid PDF.',
                ], 404);
            }
        }

        $workcenter_root = WorkcenterStructure::with('childrenRecursive')->find($workcenter_structure_id);
        $workcenter_structures = collect([$workcenter_root])->merge($this->flattenChildren($workcenter_root->childrenRecursive));

        if ($info_input_all == 'false') {
            $workcenters = collect([$workcenter_root]);
        } else {
            $workcenters = $workcenter_structures;
        }

        if ($workcenter_root) {

            $directoryPath = 'workcenter_files/' . $structureCode;
            $fileName = $structureCode . '_workinstruction.pdf';
            $fileUrl = null;

            try {

                if ($file) {

                    $path = $file->storeAs($directoryPath, $fileName, 'public');
                    $fileUrl = Storage::url($path);

                    foreach ($workcenters as $workcenter) {

                        $workcenterFile = WorkcenterFile::where('workcenter_structure_id','=',$workcenter->id)->first();

                        if ($workcenterFile) {

                            $workcenterFile->path = $path;
                            $workcenterFile->user_update_id = Auth::id();
                            $workcenterFile->save();

                        } else {

                            WorkcenterFile::create([
                                'workcenter_structure_id' => $workcenter->id,
                                'path' => $path,
                                'user_create_id' => Auth::id()
                            ]);

                        }

                        $workcenter->multibatch = $multibatch;
                        $workcenter->user_update_id = Auth::id();
                        $workcenter->save();

                    }

                } else {

                    foreach ($workcenters as $workcenter) {

                        $workcenter->multibatch = $multibatch;
                        $workcenter->user_update_id = Auth::id();
                        $workcenter->save();

                    }

                }

                $returnData = array(
                    'status' => 'success',
                    'message' => 'Success',
                    'file_url' => $fileUrl,
                );
                return response()->json($returnData, 200);
            } catch (\Throwable $e) {
                $returnData = array(
                    'status' => 'error',
                    'message' => $e->getMessage(),
                );
                return response()->json($returnData, 404);
            }
        } else {

            $returnData = array(
                'status' => 'error',
                'message' => 'Workcenter not found',
            );
            return response()->json($returnData, 404);
        }
    }

    public function getWorkCenterCharacteristic($id) {
        $workcenter = WorkcenterStructure::with('characteristics')->find($id);

        $characteristics = Characteristic::whereDoesntHave('workcenters', function ($query) use ($id) {
            $query->where('workcenter_structures.id', operator: $id);
        })->get();

        $characteristics_validation = Characteristic::whereDoesntHave('workcenters', function ($query) use ($id) {
            $query->where('workcenter_structures.id', operator: $id);
        })->where("type", "=", "validation")->get();

        $characteristics_setup= Characteristic::whereDoesntHave('workcenters', function ($query) use ($id) {
            $query->where('workcenter_structures.id', operator: $id);
        })->where("type", "=", "setup")->get();

        return view('workcenter.workcenter_template_validation', [
            'characteristics' => $characteristics,
            'characteristics_validation' => $characteristics_validation,
            'characteristics_setup' => $characteristics_setup,
            'workcenter' => $workcenter,
        ])->render();

    }

    public function searchAvailableValidationCharacteristics($id,$search )
    {

        $results = Characteristic::where('type', 'validation')
            ->where('is_active', 1)
            ->whereDoesntHave('workcenters', function ($query) use ($id) {
                $query->where('workcenter_structures.id', $id);
            })
            ->where('description', 'like', '%' . $search . '%')
            ->get()
            ->map(function ($item) {
                return [
                    'ID' => $item->id,
                    'DESCRIPTION' => $item->description,
                ];
            });

        return response()->json($results);
    }

    public function deleteCharacteristicTemplate($id, $workcenter_structure_id)
    {

        try {
            $parentId = null;
            $rootStructure = WorkcenterStructure::with('childrenRecursive')->findOrFail($workcenter_structure_id);
            $structures = collect([$rootStructure])->merge($this->flattenChildren($rootStructure->childrenRecursive));

            foreach ($structures as $key => $structure) {

                if ($parentId == null) {

                    DB::table('workcenter_template')
                    ->where('id', $id)
                    ->update([
                        'deleted_at' => now(),
                        'user_update_id' => Auth::id(),
                    ]);

                    $parentId = $id;

                } else {

                    $pivotRow = DB::table('workcenter_template')
                        ->where('parent_id', $parentId)
                        ->where('workcenter_structure_id', $structure->id)
                        ->whereNull('deleted_at')
                        ->latest('id')
                        ->first();

                    DB::table('workcenter_template')
                        ->where('parent_id', $parentId)
                        ->where('workcenter_structure_id', $structure->id)
                        ->update([
                            'deleted_at' => now(),
                            'user_update_id' => Auth::id(),
                        ]);

                    $parentId = $pivotRow->id ?? null;

                }

            }

            return response()->json([
                'success' => true,
                'message' => 'Characteristic deleted successfully.',
            ]);

        } catch (\Throwable $e) {
            $returnData = array(
                'success' => false,
                'message' => 'Error (deleteCharacteristicTemplate): ' . $e->getMessage()
            );
            return response()->json($returnData, 500);
        }

    }

    public function searchAvailableSetupCharacteristics($id,$search )
    {

        $results = Characteristic::where('type', 'setup')
            ->where('is_active', 1)
            ->whereDoesntHave('workcenters', function ($query) use ($id) {
                $query->where('workcenter_structures.id', $id);
            })
            ->where('description', 'like', '%' . $search . '%')
            ->get()
            ->map(function ($item) {
                return [
                    'ID' => $item->id,
                    'DESCRIPTION' => $item->description,
                ];
            });

        return response()->json($results);
    }

    public function searchGroupCharacteristics($id, $search)
    {
        $characteristics_groups = CharacteristicGroup::query()
            ->whereNotIn('id', function ($query) use ($id) {
                $query->select('characteristic_group_id')
                      ->from('workcenter_template')
                      ->where('workcenter_structure_id', $id)
                      ->whereNull('deleted_at')
                      ->whereNotNull('characteristic_group_id');
            })
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->get()
            ->map(function ($item) {
                return [
                    'ID' => $item->id,
                    'DESCRIPTION' => $item->name,
                ];
            });

        return response()->json($characteristics_groups);
    }

    public function createTemplateSetup($id, Request $request) {

        $workcenter = WorkcenterStructure::findOrFail($id);

        $groupId = $request->query('group_id');
        $characteristic_description = $request->query('characteristic_description');
        $characteristic_cols = $request->query('characteristic_cols');
        $characteristic_order = $request->query('characteristic_order');
        $characteristic_id = $request->query('characteristic_id');
        $template_id = $request->query('template_id');

        $maxOrder = DB::table('workcenter_template')
            ->join('characteristics', 'workcenter_template.characteristic_id', '=', 'characteristics.id')
            ->where('workcenter_structure_id', $workcenter->id)
            ->whereNull('workcenter_template.deleted_at')
            ->where('characteristics.type', 'validation')
            ->max('order');

        $maxGroupOrder = DB::table('workcenter_template')
            ->where('workcenter_structure_id', $workcenter->id)
            ->where('characteristic_group_id', $groupId)
            ->whereNull('workcenter_template.deleted_at')
            ->max('characteristic_group_order');

        $maxGroupCharacteristicOrder = DB::table('workcenter_template')
            ->join('characteristics', 'workcenter_template.characteristic_id', '=', 'characteristics.id')
            ->where('workcenter_structure_id', $workcenter->id)
            ->where('characteristic_group_id', $groupId)
            ->whereNull('workcenter_template.deleted_at')
            ->max('order');


        if (!$groupId) {
            return view('workcenter.workcenter_template_setup_form', [
                'characteristics' => collect(),
                'group' => null,
                'group_id' => null,
                'characteristic_description' => $characteristic_description,
                'workcenter' => $workcenter,
                'maxOrder' => $maxOrder
            ])->render();
        }

        $characteristics = DB::table('workcenter_template')
            ->join('characteristics', 'workcenter_template.characteristic_id', '=', 'characteristics.id')
            ->where('workcenter_template.workcenter_structure_id', $id)
            ->where('workcenter_template.characteristic_group_id', $groupId)
            ->whereNull('workcenter_template.deleted_at')
            ->select('characteristics.*', 'workcenter_template.id as template_id', 'workcenter_template.cols', 'workcenter_template.order')
            ->get();

        $group = DB::table('characteristic_groups')->find($groupId);

        $groupOrder = DB::table('workcenter_template')
        ->where('workcenter_structure_id', $workcenter->id)
        ->where('characteristic_group_id', $groupId)
        ->whereNull('deleted_at')
        ->value('characteristic_group_order');

        if ($groupOrder === null && $group) {
             $groupOrder = $group->group_order;
        }

        return view('workcenter.workcenter_template_setup_form', [
            'characteristics' => $characteristics,
            'group' => $group,
            'group_id' => $groupId,
            'workcenter' => $workcenter,
            'characteristic_description' => $characteristic_description,
            'characteristic_cols' => $characteristic_cols,
            'characteristic_order' => $characteristic_order,
            'characteristic_id' => $characteristic_id,
            'template_id' => $template_id,
            'groupOrder' => $groupOrder,
            'maxOrder' => $maxOrder + 1,
            'maxGroupOrder' => $maxGroupOrder + 1,
            'maxGroupCharacteristicOrder' => $maxGroupCharacteristicOrder + 1,
        ])->render();
    }

    public function showTableSetup ($id) {
        $workcenter = WorkcenterStructure::find($id);
        $characteristics = Characteristic::where('type', 'setup')
            ->whereDoesntHave('workcenters', function ($query) use ($id) {
                $query->where('workcenter_structures.id', $id);
            })
        ->get();

        $characteristics_group = DB::table('workcenter_template')
        ->join('characteristics', 'workcenter_template.characteristic_id', '=', 'characteristics.id')
        ->join('characteristic_groups', 'workcenter_template.characteristic_group_id', '=', 'characteristic_groups.id')
        ->where('workcenter_template.workcenter_structure_id', $id)
        ->where('characteristics.type', 'setup')
        ->whereNull('workcenter_template.deleted_at')
        ->orderBy('workcenter_template.characteristic_group_order')
        ->select(
            'characteristics.id',
            'characteristics.description',
            'characteristics.type',
            'workcenter_template.cols',
            'workcenter_template.order',
            'workcenter_template.characteristic_group_order',
            'characteristic_groups.id as group_id',
            'characteristic_groups.name as group_name',
        )
        ->get();

        return view('workcenter.workcenter_template_setup', [
            'characteristics' => $characteristics,
            'workcenter' => $workcenter,
            'characteristics_group' => $characteristics_group,
        ])->render();
    }

    public function templateCharacteristicSetupRemove ($template_id, $workcenter_structure_id)
    {

        try {
            $parentId = null;
            $rootStructure = WorkcenterStructure::with('childrenRecursive')->findOrFail($workcenter_structure_id);
            $structures = collect([$rootStructure])->merge($this->flattenChildren($rootStructure->childrenRecursive));

            foreach ($structures as $key => $structure) {

                if ($parentId == null) {

                    DB::table('workcenter_template')
                    ->where('id', $template_id)
                    ->where('workcenter_structure_id', $workcenter_structure_id)
                    ->update([
                        'deleted_at' => now(),
                        'user_update_id' => Auth::id(),
                    ]);

                    $parentId = $template_id;

                } else {

                    $pivotRow = DB::table('workcenter_template')
                        ->where('parent_id', $parentId)
                        ->where('workcenter_structure_id', $structure->id)
                        ->whereNull('deleted_at')
                        ->latest('id')
                        ->first();

                    DB::table('workcenter_template')
                        ->where('parent_id', $parentId)
                        ->where('workcenter_structure_id', $structure->id)
                        ->update([
                            'deleted_at' => now(),
                            'user_update_id' => Auth::id(),
                        ]);

                    $parentId = $pivotRow->id ?? null;

                }

            }

            return response()->json([
                'success' => true,
                'message' => 'Characteristic deleted successfully.',
            ]);

        } catch (\Throwable $e) {
            $returnData = array(
                'success' => false,
                'message' => 'Error (templateCharacteristicSetupRemove): ' . $e->getMessage()
            );
            return response()->json($returnData, 500);
        }

    }

    public function getWorkCenterGroupCharacteristic(Request $request, $id, $characteristic_group_id)
    {
        $pageLength = intval($request->length ?? 0);
        $skip = intval($request->start ?? 0);

        $orderColumnIndex = $request->order[0]['column'] ?? '0';
        $orderBy = $request->order[0]['dir'] ?? 'desc';


        $query = DB::table('workcenter_template')
        ->join('characteristics', 'workcenter_template.characteristic_id', '=', 'characteristics.id')
        ->join('characteristic_groups', 'workcenter_template.characteristic_group_id', '=', 'characteristic_groups.id')
        ->where('workcenter_template.workcenter_structure_id', $id)
        ->where('workcenter_template.characteristic_group_id', $characteristic_group_id)
        ->where('characteristics.type', 'setup')
        ->whereNull('workcenter_template.deleted_at')
        ->orderBy('workcenter_template.order')
        ->select(
            'characteristics.id',
            'characteristics.description',
            'characteristics.type',
            'workcenter_template.cols',
            'workcenter_template.order',
            'workcenter_template.id as template_id',
            'characteristic_groups.id as group_id',
            'characteristic_groups.name as group_name'
        );

        $search_arr = $request->search;
        $search = $search_arr['value'] ?? '';

        $query = $query->where(function($query) use ($search){
            if($search != "") {
                $query->orWhere('characteristics.description', 'like', "%".$search."%");
                $query->orWhere('workcenter_template.cols', 'like', "%".$search."%");
                $query->orWhere('workcenter_template.order', 'like', "%".$search."%");
            }
        });

        $orderByName = match ($orderColumnIndex) {
            '0' => 'workcenter_template.order',
            '1' => 'characteristics.description',
            '2' => 'workcenter_template.cols',
            default => 'workcenter_template.order'
        };

        $query = $query->orderBy($orderByName, $orderBy);

        $recordsFiltered = $recordsTotal = $query->count();

        if ($pageLength > 0) {
            $data = $query->skip($skip)->take($pageLength)->get();
        } else {
            $data = $query->get();
        }

        return response()->json([
            'draw' => intval($request->draw ?? 0),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ], 200);
    }

    public function removeTemplateSetup($group_id, Request $request)
    {

        $workcenter_structure_id = $request->input('workcenter_structure_id');

        try {
            $characteristic_group_id = null;
            $rootStructure = WorkcenterStructure::with('childrenRecursive')->findOrFail($workcenter_structure_id);
            $structures = collect([$rootStructure])->merge($this->flattenChildren($rootStructure->childrenRecursive));

            foreach ($structures as $key => $structure) {

                if ($characteristic_group_id == null) {

                    DB::table('workcenter_template')
                    ->where('characteristic_group_id', $group_id)
                    ->where('workcenter_structure_id', $workcenter_structure_id)
                    ->update([
                        'deleted_at' => now(),
                        'user_update_id' => Auth::id(),
                    ]);

                    $characteristic_group_id = $group_id;

                } else {

                    DB::table('workcenter_template')
                    ->where('characteristic_group_id', $characteristic_group_id)
                    ->where('workcenter_structure_id', $structure->id)
                    ->update([
                        'deleted_at' => now(),
                        'user_update_id' => Auth::id(),
                    ]);

                }

            }

            return response()->json([
                'success' => true,
                'message' => 'Characteristic Group removed successfully.',
            ]);

        } catch (\Throwable $e) {
            $returnData = array(
                'success' => false,
                'message' => 'Error (deleteCharacteristicTemplate): ' . $e->getMessage()
            );
            return response()->json($returnData, 500);
        }

    }

    public function generateLabel(Request $request)
    {
        $workcenter_structure_code = $request->input('workcenter_structure_code');
        $workcenterId = $request->input('workcenter_structure_id');

        $rootStructure = WorkcenterStructure::with('childrenRecursive')->findOrFail($workcenterId);
        $structures = collect([$rootStructure])->merge($this->flattenChildren($rootStructure->childrenRecursive));

        $structureIds = $structures->pluck('id')->toArray();

        $structureCodes = DB::table('workcenter_structures')
        ->whereIn('id', $structureIds)
        ->where('structure_type', 'WC')
        ->select('id', 'structure_code', 'structure_name', 'structure_type')
        ->get();

        $structuresMap = [];
        foreach ($structureCodes as $structure) {
            $structuresMap[$structure->id] = $structure;
        }

        $labels = [];

        foreach ($structures as $structure) {

            if (isset($structuresMap[$structure->id]) && !empty($structuresMap[$structure->id]->structure_code)) {
                $structureData = $structuresMap[$structure->id];

                $qrCode = QrCode::size(100)->generate($structureData->structure_code)->__tostring();

                $labels[] = [
                    'id' => $structure->id,
                    'code' => $structureData->structure_code,
                    'name' => $structureData->structure_name,
                    'type' => $structureData->structure_type,
                    'qrcode' => $qrCode
                ];
            }
   
        }

        return response()->json([
            'success' => true,
            'labels' => $labels,
            'total' => count($labels),
        ]);
    }

    public function getGroupOrder(Request $request)
    {
        try {
            $groupId = $request->input('group_id');

            $group = CharacteristicGroup::findOrFail($groupId);

            return response()->json([
                'success' => true,
                'order' => $group->group_order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar a ordem do grupo: ' . $e->getMessage()
            ], 500);
        }
    }

    // public function getCharacteristicUOM (Request $request)
    // {
    //     try {
    //         $characteristicId = $request->input('characteristic_id');

    //         $characteristic = Characteristic::findOrFail($characteristicId);

    //         return response()->json([
    //             'success' => true,
    //             'uom' => $characteristic->uom
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Error fetching UOM: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function updateGroupOrder (Request $request)
    {
        DB::beginTransaction();
        try {

            $rootStructure = WorkcenterStructure::with('childrenRecursive')->findOrFail($request->workcenter_structure_id);
            $structures = collect([$rootStructure])->merge($this->flattenChildren($rootStructure->childrenRecursive));

            foreach ($structures as $structure) {

                foreach ($request->novasOrdems as $item) {

                    DB::table('workcenter_template')
                    ->where('workcenter_structure_id', $structure->id)
                    ->where('characteristic_group_id', $item['id'])
                    ->whereNull('deleted_at')
                    ->update([
                        'characteristic_group_order' => $item['ordemDestino'],
                        'user_update_id' => Auth::id(),
                        'updated_at' => now()
                    ]);

                }

            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Characteristic Group Reorder successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateCharacteristicOrder (Request $request)
    {
        DB::beginTransaction();
        try {

            $rootStructure = WorkcenterStructure::with('childrenRecursive')->findOrFail($request->workcenter_structure_id);
            $structures = collect([$rootStructure])->merge($this->flattenChildren($rootStructure->childrenRecursive));

            foreach ($structures as $structure) {

                foreach ($request->novasOrdems as $item) {

                    DB::table('workcenter_template')
                    ->where('workcenter_structure_id', $structure->id)
                    ->where('characteristic_id', $item['id'])
                    ->whereNull('deleted_at')
                    ->update([
                        'order' => $item['ordemDestino'],
                        'user_update_id' => Auth::id(),
                        'updated_at' => now()
                    ]);

                }

            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Characteristic Reorder successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function insertWorkcenterTemplate(?int $parentId, array $arrData, bool $isTemplateSetup = false)
    {
        try {

            $data = [];
            $workcenter = WorkcenterStructure::findOrFail($arrData['workcenter_structure_id']);

            if ($isTemplateSetup) {

                $data = [
                    'cols' => $arrData['cols'],
                    'order' => $arrData['order'],
                    'characteristic_group_id' => $arrData['groupId'],
                    'characteristic_group_order' => $arrData['characteristicGroupOrder'],
                ];

            } else {

                $data = [
                    'cols' => $arrData['cols'],
                    'order' => $arrData['order'],
                ];

            }

            $exists = DB::table('workcenter_template')
            ->where('workcenter_structure_id', $workcenter->id)
            ->where('characteristic_id', $arrData['characteristicId'])
            ->whereNull('deleted_at')
            ->exists();

            if ($exists) {

                $data['parent_id'] = $parentId;
                $data['user_update_id'] = Auth::id();

                $workcenter->characteristics()->updateExistingPivot($arrData['characteristicId'],$data);

            } else {

                $data['parent_id'] = $parentId;
                $data['user_create_id'] = Auth::id();

                $workcenter->characteristics()->attach([
                    $arrData['characteristicId'] => $data
                ]);

            }

            $pivotRow = DB::table('workcenter_template')
                ->where('workcenter_structure_id', $workcenter->id)
                ->where('characteristic_id', $arrData['characteristicId'])
                ->whereNull('deleted_at')
                ->latest('id')
                ->first();

            if (!$pivotRow) {
                throw new WorkcenterTemplateException("Failed to get inserted template.");
            }

            return ['success' => true, 'message' => 'Characteristic inserted successfully.', 'parentId' => $pivotRow->id];

        } catch (\Throwable $e) {
            throw new WorkcenterTemplateException("Error (insertWorkcenterTemplate): " . $e->getMessage());
        }
    }

    public function updateWorkcenterTemplate(?int $parentId, array $arrData, bool $isTemplateSetup = false)
    {
        try {
            $workcenter = WorkcenterStructure::findOrFail($arrData['workcenter_structure_id']);

            if ($isTemplateSetup) {

                $data = [
                    'cols' => $arrData['cols'],
                    'order' => $arrData['order'],
                    'characteristic_group_id' => $arrData['groupId'],
                    'characteristic_group_order' => $arrData['characteristicGroupOrder'],
                    'user_update_id' => Auth::id(),
                    'updated_at' => now()
                ];

            } else {

                $data = [
                    'cols' => $arrData['cols'],
                    'order' => $arrData['order'],
                    'user_update_id' => Auth::id(),
                    'updated_at' => now()
                ];

            }

            if ($parentId === null) {
                $workcenter->characteristics()->updateExistingPivot($arrData['characteristicId'], $data);

                $row = DB::table('workcenter_template')
                    ->where('workcenter_structure_id', $workcenter->id)
                    ->where('characteristic_id', $arrData['characteristicId'])
                    ->whereNull('deleted_at')
                    ->latest('id')
                    ->first();

                return ['success' => true, 'message' => 'Characteristic updated successfully.', 'parentId' => $row->id ?? null];
            } else {
                $template = DB::table('workcenter_template')
                    ->where('workcenter_structure_id', $workcenter->id)
                    ->where('characteristic_id', $arrData['characteristicId'])
                    ->where('parent_id', $parentId)
                    ->whereNull('deleted_at')
                    ->latest('id')
                    ->first();

                if ($template) {

                    DB::table('workcenter_template')
                        ->where('id', $template->id)
                        ->update($data);

                    return ['success' => true, 'message' => 'Characteristic updated successfully.', 'parentId' => $template->id];

                } else {

                    $template = DB::table('workcenter_template')
                    ->where('workcenter_structure_id', $workcenter->id)
                    ->where('characteristic_id', $arrData['characteristicId'])
                    ->whereNull('deleted_at')
                    ->latest('id')
                    ->first();

                    if ($template) {

                        $data['parent_id'] = $parentId;

                        DB::table('workcenter_template')
                            ->where('id', $template->id)
                            ->update($data);

                        return ['success' => true, 'message' => 'Characteristic updated successfully.', 'parentId' => $template->id];
                    }
                }

                return ['success' => false, 'message' => 'No template found.'];
            }

        } catch (\Throwable $e) {
            throw new WorkcenterTemplateException("Error while updating (updateWorkcenterTemplate): " . $e->getMessage());
        }
    }

    private function deleteCharacteristicRecursive(WorkcenterStructure $structure, ?int $parentId, int $template_id): void
    {
        $currentStructureId = $structure->id;

        if ($parentId === null) {
            $pivotRow = DB::table('workcenter_template')
                ->where('id', $template_id)
                ->where('workcenter_structure_id', $currentStructureId)
                ->whereNull('deleted_at')
                ->latest('id')
                ->first();

            if ($pivotRow) {
                DB::table('workcenter_template')
                    ->where('id', $pivotRow->id)
                    ->update([
                        'deleted_at' => now(),
                        'user_update_id' => Auth::id(),
                    ]);

                $parentId = $pivotRow->id;
            }

        } else {

            $pivotRow = DB::table('workcenter_template')
                ->where('parent_id', $parentId)
                ->where('workcenter_structure_id', $currentStructureId)
                ->whereNull('deleted_at')
                ->latest('id')
                ->first();

            if ($pivotRow) {
                DB::table('workcenter_template')
                    ->where('id', $pivotRow->id)
                    ->update([
                        'deleted_at' => now(),
                        'user_update_id' => Auth::id(),
                    ]);

                $parentId = $pivotRow->id;
            } else {
                return;
            }
        }

        foreach ($structure->childrenRecursive as $childStructure) {
            $this->deleteCharacteristicRecursive($childStructure, $parentId, $template_id);
        }
    }

    private function processStructureRecursively(WorkcenterStructure $structure, ?int $parentId, array $arrData, bool $isTemplateSetup = false)
    {
        $arrData['workcenter_structure_id'] = $structure->id;
        $templateId = $arrData['template_id'];
        $result = null;

        if ($templateId !== null) {
            $result = $this->updateWorkcenterTemplate($parentId, $arrData, $isTemplateSetup);
            $currentParentId = $result['parentId'] ?? null;
        } else {
            $result = $this->insertWorkcenterTemplate($parentId, $arrData, $isTemplateSetup);
            $currentParentId = $result['parentId'] ?? null;
        }

        foreach ($structure->childrenRecursive as $child) {
            $this->processStructureRecursively($child, $currentParentId, $arrData, $isTemplateSetup);
        }

        return $result;
    }

    public function saveWorkCenterCharacteristicValidation(Request $request)
    {
        $validated = $request->validate([
            'workcenter_structure_id' => 'required|integer',
            'characteristic_id' => 'required|integer',
            'cols_input' => 'required|integer',
            'order_input' => 'required|integer',
            'template_id' => 'nullable|integer',
        ]);

        $workcenterId = $validated['workcenter_structure_id'];
        $arrData = [
            'characteristicId' => $validated['characteristic_id'],
            'cols' => $validated['cols_input'],
            'order' => $validated['order_input'],
            'template_id' => $validated['template_id'],
        ];

        try {
            $structure = WorkcenterStructure::with('childrenAllRecursive')->findOrFail($workcenterId);

            $pivot = $this->processStructureRecursively($structure, null, $arrData, false);

            return response()->json([
                'success' => true,
                'message' => 'Processing completed successfully.',
                'template_id' => $pivot['parentId'] ?? null,
            ], 200);

        } catch (WorkcenterTemplateException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error (saveWorkCenterCharacteristicValidation): ' . $e->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error (saveWorkCenterCharacteristicValidation): ' . $e->getMessage(),
            ], 500);
        }
    }

    public function saveWorkCenterCharacteristicSetup(Request $request)
    {
        $validated = $request->validate([
            'groupCharacteristic_id' => 'required|exists:characteristic_groups,id',
            'workcenter_structure_id' => 'required|exists:workcenter_structures,id',
            'characteristic_id' => 'required|exists:characteristics,id',
            'cols_input' => 'required|integer',
            'order_input' => 'required|integer',
            'template_id' => 'nullable|exists:workcenter_template,id',
            'characteristic_group_order' => 'nullable|integer',
        ]);

        $workcenterId = $validated['workcenter_structure_id'];
        $arrData = [
            'groupId' => $validated['groupCharacteristic_id'],
            'characteristicId' => $validated['characteristic_id'],
            'cols' => $validated['cols_input'],
            'order' => $validated['order_input'],
            'template_id' => $validated['template_id'],
            'characteristicGroupOrder' => $validated['characteristic_group_order'],
        ];

        try {
            $structure = WorkcenterStructure::with('childrenAllRecursive')->findOrFail($workcenterId);

            $pivot = $this->processStructureRecursively($structure, null, $arrData, true);

            return response()->json([
                'success' => true,
                'message' => 'Processing completed successfully.',
                'template_id' => $pivot['parentId'] ?? null,
            ], 200);

        } catch (WorkcenterTemplateException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error (saveWorkCenterCharacteristicTemplate): ' . $e->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error (saveWorkCenterCharacteristicTemplate): ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteCharacteristicFromStructure(int $template_id,int $workcenter_structure_id)
    {
        try {
            $structure = WorkcenterStructure::with('childrenAllRecursive')->findOrFail($workcenter_structure_id);
            $this->deleteCharacteristicRecursive($structure, null, $template_id);

            return response()->json([
                'success' => true,
                'message' => 'Characteristic successfully removed from the structure and its descendants.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing characteristic (deleteCharacteristicFromStructure): ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteCharacteristicGroupFromStructure(int $group_id,int $workcenter_structure_id)
    {

        try {
            $structure = WorkcenterStructure::with('childrenAllRecursive')->findOrFail($workcenter_structure_id);

            $TemplateGroups = DB::table('workcenter_template')
            ->where('workcenter_structure_id', $structure->id)
            ->where('characteristic_group_id', $group_id)
            ->whereNull('deleted_at')
            ->select('id')
            ->orderBy('id')
            ->get();

            foreach ($TemplateGroups as $TemplateGroup) {
                $this->deleteCharacteristicRecursive($structure, null, $TemplateGroup->id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Characteristic Group successfully removed from the structure and its descendants.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing characteristic group (deleteCharacteristicGroupFromStructure): ' . $e->getMessage(),
            ], 500);
        }
    }

    public function showHierarchyContext($id, $separator = ' « ', $reverse = true)
    {
        $structure = WorkcenterStructure::with('parent.children', 'parent.parent')->findOrFail($id);

        return response()->json($structure->getHierarchyContext($separator, $reverse));
    }

    public function getMaxOrderCharacteristicAndGroup($id, $characteristic_group_id = null)
    {
        try{
            $workcenter = WorkcenterStructure::findOrFail($id);
            $maxOrder = DB::table('workcenter_template')
                ->join('characteristics', 'workcenter_template.characteristic_id', '=', 'characteristics.id')
                ->where('workcenter_structure_id', $workcenter->id)
                ->whereNull('workcenter_template.deleted_at')
                ->where('characteristics.type', 'validation')
                ->max('order');

            $maxGroupOrder = DB::table('workcenter_template')
                ->where('workcenter_structure_id', $workcenter->id)
                ->where('characteristic_group_id', $characteristic_group_id)
                ->whereNull('workcenter_template.deleted_at')
                ->max('characteristic_group_order');

            $maxGroupCharacteristicOrder = DB::table('workcenter_template')
                ->join('characteristics', 'workcenter_template.characteristic_id', '=', 'characteristics.id')
                ->where('workcenter_structure_id', $workcenter->id)
                ->where('characteristic_group_id', $characteristic_group_id)
                ->whereNull('workcenter_template.deleted_at')
                ->max('order');

            return response()->json([
                'success' => true,
                'maxOrder' => $maxOrder,
                'maxGroupOrder' => $maxGroupOrder,
                'maxGroupCharacteristicOrder' => $maxGroupCharacteristicOrder,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error (getMaxOrderCharacteristicAndGroup): ' . $e->getMessage(),
            ], 500);
        }

    }

    public function duplicateTemplateValidation ($id) {

        $workcenter_id = $id;

        $rootStructure = WorkcenterStructure::with('parent.children', 'parent.parent')->find($workcenter_id);
        $rootStructure->getHierarchyContext(' << ',true);

        $hierarchy_machines = $rootStructure->getHierarchyContext($workcenter_id,' << ',true);
     
        return view('workcenter.duplicate.workcenter_duplicate_template_validation', [
            'workcenter_id' => $workcenter_id,
            'hierarchy_machines' => $hierarchy_machines,
        ])->render();
    }

    public function confirmDuplicateValidation(Request $request) {
        $workcenter_id = $request->input('workcenter_id');
        $machine_selected_id = $request->input('machine_selected_id');

        $workcenter = WorkcenterStructure::with('children')->find($workcenter_id);

        $this->saveDuplicateValidation($workcenter_id, $machine_selected_id, $workcenter->structure_parent_id);

        foreach ($workcenter->children as $child) {
            $this->saveDuplicateValidation($child->id, $machine_selected_id, $workcenter->structure_parent_id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Duplicate validation saved successfully.',
            'workcenter_id' => $workcenter_id, 
        ]);
    }

    public function saveDuplicateValidation ($workcenter_id, $machine_selected_id, $workcenter_structure_parentid) {
        $template_selected_machine = DB::table('workcenter_template')
        ->join('characteristics', 'workcenter_template.characteristic_id', '=', 'characteristics.id') 
        ->where('workcenter_template.workcenter_structure_id', $machine_selected_id) 
        ->where('characteristics.type', 'validation') 
        ->whereNull('workcenter_template.deleted_at') 
        ->whereNull('characteristics.deleted_at') 
        ->get();

        foreach ($template_selected_machine as $template) {
            DB::table('workcenter_template')->insert([
                'workcenter_structure_id' => $workcenter_id, 
                'characteristic_id' => $template->characteristic_id,
                'cols' => $template->cols,
                'order' => $template->order,
                'characteristic_group_id' => $template->characteristic_group_id,
                'characteristic_group_order' => $template->characteristic_group_order,
                'parent_id' => $workcenter_structure_parentid,
                'user_create_id' => Auth::id(),
                'user_update_id' => Auth::id(),
                'created_at' => now(), 
                'updated_at' => now(),
                'deleted_at' => null 
            ]);
        }
    }

    public function duplicateTemplateSetup ($id) {

        $workcenter_id = $id;
        
        $rootStructure = WorkcenterStructure::with('parent.children', 'parent.parent')->find($workcenter_id);
        $rootStructure->getHierarchyContext(' << ',true);

        $hierarchy_machines = $rootStructure->getHierarchyContext($workcenter_id,' << ',true);

        return view('workcenter.duplicate.workcenter_duplicate_template_setup', [
            'workcenter_id' => $workcenter_id,
            'hierarchy_machines' => $hierarchy_machines,
        ])->render();
    }

    public function confirmDuplicateSetup (Request $request) {
        $workcenter_id = $request->input('workcenter_id');
        $machine_selected_id = $request->input('machine_selected_id');

        $workcenter = WorkcenterStructure::with('children')->find($workcenter_id);

        $this->saveDuplicateSetup($workcenter_id, $machine_selected_id, $workcenter->structure_parent_id);

        foreach ($workcenter->children as $child) {
            $this->saveDuplicateSetup($child->id, $machine_selected_id, $workcenter->structure_parent_id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Duplicate validation saved successfully.',
            'workcenter_id' => $workcenter_id, 
        ]);
    }

    public function saveDuplicateSetup ($workcenter_id, $machine_selected_id, $workcenter_structure_parentid) {
        $template_selected_machine = DB::table('workcenter_template')
        ->join('characteristics', 'workcenter_template.characteristic_id', '=', 'characteristics.id') 
        ->where('workcenter_template.workcenter_structure_id', $machine_selected_id) 
        ->where('characteristics.type', 'setup') 
        ->whereNull('workcenter_template.deleted_at') 
        ->whereNull('characteristics.deleted_at') 
        ->get();

        foreach ($template_selected_machine as $template) {
            DB::table('workcenter_template')->insert([
                'workcenter_structure_id' => $workcenter_id, 
                'characteristic_id' => $template->characteristic_id,
                'cols' => $template->cols,
                'order' => $template->order,
                'characteristic_group_id' => $template->characteristic_group_id,
                'characteristic_group_order' => $template->characteristic_group_order,
                'parent_id' => $workcenter_structure_parentid,
                'user_create_id' => Auth::id(),
                'user_update_id' => Auth::id(),
                'created_at' => now(), 
                'updated_at' => now(),
                'deleted_at' => null 
            ]);
        }
    }

    public function toggleCritical(Request $request)
    {
            $workcenterId = $request->input('workcenterId');
            $isCritical = $request->input('isChecked') ? 1 : 0;

            try {
                DB::table('workcenter_structures')
                    ->where('id', $workcenterId)
                    ->update(['isCritical' => $isCritical]);

                return response()->json([
                    'success' => true,
                    'isCritical' => $isCritical,
                    'message' => 'Workcenter critical state updated successfully.',
                ]);
            } catch (\Throwable $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao atualizar estado crítico: ' . $e->getMessage(),
                ], 500);
            }
    }

    public function toggleDepartmentOrder (Request $request) {
        $workcenterId = $request->input('workcenterId');
        $departmentOrder = $request->boolean('isChecked') ? 1 : 0;

        try {
            DB::table('workcenter_structures')
                ->where('id', $workcenterId)
                ->update(['departmentOrder' => $departmentOrder]);

            return response()->json([
                'success' => true,
                'isCritical' => $departmentOrder,
                'message' => 'Department Order state updated successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar estado crítico: ' . $e->getMessage(),
            ], 500);
        }
    }
}
