<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class SimulationController extends Controller
{
    private function ensureTables()
    {
        // 🟢 scenarios 테이블 및 memo 컬럼 검증/생성
        if (!Schema::hasTable('scenarios')) {
            Schema::create('scenarios', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('memo')->nullable();
                $table->timestamps();
            });
            DB::table('scenarios')->insert(['name' => '基本シナリオ', 'created_at' => now()]);
        } else {
            if (!Schema::hasColumn('scenarios', 'memo')) {
                Schema::table('scenarios', function (Blueprint $table) {
                    $table->text('memo')->nullable()->after('name');
                });
            }
        }
        
        // 🟢 placements 테이블 부재 시 신규 자동 생성
        if (!Schema::hasTable('placements')) {
            Schema::create('placements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('scenario_id')->default(1);
                $table->string('sponsor_id');
                $table->string('node_id');
                $table->string('full_code')->nullable();
                $table->string('recommender_id')->nullable();
                $table->string('line_type')->default('A');
                $table->integer('period_num')->default(1);
                $table->string('period_label')->default('1-前');
                $table->integer('pv')->default(700000);
                $table->integer('cv')->default(700000);
                $table->timestamps();
            });
        } else {
            if (!Schema::hasColumn('placements', 'scenario_id')) {
                Schema::table('placements', function (Blueprint $table) {
                    $table->unsignedBigInteger('scenario_id')->default(1)->after('id');
                });
            }
            if (!Schema::hasColumn('placements', 'pv')) {
                Schema::table('placements', function (Blueprint $table) {
                    $table->integer('pv')->default(700000)->after('period_label');
                    $table->integer('cv')->default(700000)->after('pv');
                });
            }
        }
    }

    public function getScenarios()
    {
        $this->ensureTables();
        $scenarios = DB::table('scenarios')->orderBy('id', 'asc')->get();
        return response()->json(['status' => 'success', 'data' => $scenarios]);
    }

    public function saveScenario(Request $request)
    {
        $this->ensureTables();
        $name = $request->input('name', '新規シナリオ');
        $sourceId = $request->input('source_id', 1);

        DB::beginTransaction();
        try {
            $sourceScenario = DB::table('scenarios')->where('id', $sourceId)->first();
            $memo = $request->input('memo', $sourceScenario->memo ?? null);

            $newId = DB::table('scenarios')->insertGetId([
                'name' => $name,
                'memo' => $memo,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $placements = DB::table('placements')->where('scenario_id', $sourceId)->get();
            $insertData = [];
            foreach ($placements as $p) {
                $insertData[] = [
                    'scenario_id' => $newId,
                    'sponsor_id' => $p->sponsor_id,
                    'node_id' => $p->node_id,
                    'full_code' => $p->full_code,
                    'line_type' => $p->line_type,
                    'period_num' => $p->period_num,
                    'period_label' => $p->period_label,
                    'pv' => $p->pv ?? 700000,
                    'cv' => $p->cv ?? 700000,
                ];
            }
            if (!empty($insertData)) {
                foreach (array_chunk($insertData, 500) as $chunk) {
                    DB::table('placements')->insert($chunk);
                }
            }

            DB::commit();
            return response()->json(['status' => 'success', 'data' => ['id' => $newId, 'name' => $name, 'memo' => $memo]]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function updateMemo(Request $request, $id)
    {
        $this->ensureTables();
        $memo = $request->input('memo');

        try {
            DB::table('scenarios')
                ->where('id', $id)
                ->update([
                    'memo' => $memo,
                    'updated_at' => now()
                ]);

            return response()->json([
                'status' => 'success',
                'message' => '메모가 성공적으로 저장되었습니다.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteScenario($id)
    {
        $this->ensureTables();
        try {
            DB::table('scenarios')->where('id', $id)->delete();
            DB::table('placements')->where('scenario_id', $id)->delete();
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getPlacements(Request $request)
    {
        $this->ensureTables();
        $scenarioId = $request->query('scenario_id', 1);
        $data = DB::table('placements')
            ->where('scenario_id', $scenarioId)
            ->orderBy('period_num', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    public function savePlacements(Request $request)
    {
        $this->ensureTables(); // 🟢 테이블 자동 생성 검증
        $scenarioId = $request->input('scenario_id', 1);
        $placements = $request->input('placements', []);

        DB::beginTransaction();
        try {
            DB::table('placements')->where('scenario_id', $scenarioId)->delete();

            $insertData = [];
            foreach ($placements as $p) {
                $insertData[] = [
                    'scenario_id'  => $scenarioId,
                    'sponsor_id'   => $p['sponsor_id'],
                    'node_id'      => $p['node_id'],
                    'full_code'    => $p['full_code'],
                    'line_type'    => $p['line_type'],
                    'period_num'   => $p['period_num'],
                    'period_label' => $p['period_label'],
                    'pv'           => $p['pv'] ?? 700000,
                    'cv'           => $p['cv'] ?? 700000,
                ];
            }

            if (!empty($insertData)) {
                foreach (array_chunk($insertData, 500) as $chunk) {
                    DB::table('placements')->insert($chunk);
                }
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'DB 保存完了']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function seed985(Request $request)
    {
        $this->ensureTables(); // 🟢 테이블 부재 시 신규 생성 보장

        $scenarioId = $request->input('scenario_id', 1);

        DB::beginTransaction();
        try {
            DB::table('placements')->where('scenario_id', $scenarioId)->delete();

            $maxPeriod = 128; 
            $insertData = [];
            $aCount = 1;
            $bCount = 1;

            $queue = [['id' => 'm', 'depth' => 0, 'period_num' => 1]];

            while (!empty($queue)) {
                $curr = array_shift($queue);
                $currId = $curr['id'];
                $currP = $curr['period_num'];
                $currDepth = $curr['depth'];

                $pA = $currP + 6;
                if ($pA <= $maxPeriod) {
                    $childAId = 'a' . ($aCount++);
                    $depthA = $currDepth + 1;
                    $pLabelA = ceil($pA / 2) . '-' . ($pA % 2 !== 0 ? '前' : '後');

                    $insertData[] = [
                        'scenario_id' => $scenarioId,
                        'sponsor_id'  => $currId,
                        'node_id'      => $childAId,
                        'full_code'    => "{$currId}-{$depthA}-{$childAId}",
                        'line_type'    => 'A',
                        'period_num'   => $pA,
                        'period_label' => $pLabelA,
                        'pv' => 700000,
                        'cv' => 700000,
                    ];

                    $queue[] = ['id' => $childAId, 'depth' => $depthA, 'period_num' => $pA];
                }

                $pB = $currP + 12;
                if ($pB <= $maxPeriod) {
                    $childBId = 'b' . ($bCount++);
                    $depthB = $currDepth + 1;
                    $pLabelB = ceil($pB / 2) . '-' . ($pB % 2 !== 0 ? '前' : '後');

                    $insertData[] = [
                        'scenario_id' => $scenarioId,
                        'sponsor_id'  => $currId,
                        'node_id'      => $childBId,
                        'full_code'    => "{$currId}-{$depthB}-{$childBId}",
                        'line_type'    => 'B',
                        'period_num'   => $pB,
                        'period_label' => $pLabelB,
                        'pv' => 700000,
                        'cv' => 700000,
                    ];

                    $queue[] = ['id' => $childBId, 'depth' => $depthB, 'period_num' => $pB];
                }
            }

            foreach (array_chunk($insertData, 500) as $chunk) {
                DB::table('placements')->insert($chunk);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'CROWN 달성(64ヶ月/128期) DB 構築成功']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function calculateSimulation(Request $request)
    {
        return response()->json(['status' => 'success', 'data' => []]);
    }
}