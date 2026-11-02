<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;

class McqPortalController extends Controller
{
    private function supabase(array $extraHeaders = [])
    {
        $url = rtrim(env('SUPABASE_URL'), '/') . '/rest/v1';
        $key = env('SUPABASE_KEY');

        return Http::withoutVerifying()
            ->baseUrl($url)
            ->withHeaders(array_merge([
                'apikey' => $key,
                'Authorization' => 'Bearer ' . $key,
                'Content-Type' => 'application/json',
            ], $extraHeaders));
    }

    public function index()
    {
        return view('mcq.index');
    }

    public function bootstrap()
    {
        $users = $this->supabase()->get('/mcq_users?select=username,name,role')->json() ?? [];
        $seasonsRaw = $this->supabase()->get('/seasons?select=code&order=id.asc')->json() ?? [];
        $seasons = array_column($seasonsRaw, 'code');
        $brands = $this->supabase()->get('/brands?select=id,name,color,badge')->json() ?? [];

        $boxesRaw = $this->supabase()->get('/master_boxes?select=*')->json() ?? [];
        $masterBoxes = array_map(fn($b) => [
            'id' => $b['id'],
            'brandId' => $b['brand_id'],
            'name' => $b['name'],
            'l' => (float) $b['l'],
            'w' => (float) $b['w'],
            'h' => (float) $b['h'],
            'tare' => (float) $b['tare'],
            'maxKg' => (float) $b['max_kg'],
        ], $boxesRaw);

        $testsRaw = $this->supabase()->get('/mcq_tests?select=*&order=created_at.desc')->json() ?? [];
        $tests = array_map(fn($t) => [
            'id' => $t['id'],
            'season' => $t['season'],
            'brandId' => $t['brand_id'],
            'styleCode' => $t['style_code'],
            'category' => $t['category'],
            'garmentL' => (float) $t['garment_l'],
            'garmentW' => (float) $t['garment_w'],
            'garmentH' => (float) $t['garment_h'],
            'compressionPct' => (float) ($t['compression_pct'] ?? 0),
            'lockFlat' => (bool) $t['lock_flat'],
            'unitWeightGrams' => (float) $t['unit_weight_grams'],
            'orderQty' => (int) $t['order_qty'],
            'boxName' => $t['box_name'],
            'boxL' => (float) $t['box_l'],
            'boxW' => (float) $t['box_w'],
            'boxH' => (float) $t['box_h'],
            'boxTareGrams' => (float) $t['box_tare_grams'],
            'maxWeightLimitKg' => (float) $t['max_weight_limit_kg'],
            'createdBy' => $t['created_by'] ?? 'Sistema',
            'createdAt' => $t['created_at'],
        ], $testsRaw);

        return response()->json([
            'users' => $users,
            'seasons' => $seasons,
            'brands' => $brands,
            'masterBoxes' => $masterBoxes,
            'tests' => $tests,
        ]);
    }

    public function login(Request $request)
    {
        $username = strtolower(trim($request->username));
        $users = $this->supabase()->get('/mcq_users', [
            'username' => 'eq.' . $username,
            'select' => '*'
        ])->json();

        if (empty($users)) {
            return response()->json(['error' => 'Usuario no encontrado'], 401);
        }

        $user = $users[0];
        
        // Validación directa compatible con texto plano y hashes
        $matches = ($request->password === $user['password']) || Hash::check($request->password, $user['password']);

        if (!$matches) {
            return response()->json(['error' => 'Contraseña incorrecta'], 401);
        }

        return response()->json([
            'username' => $user['username'],
            'name' => $user['name'],
            'role' => $user['role'],
        ]);
    }

    public function saveTest(Request $request)
    {
        $id = $request->id ?: 'MCQ-' . date('Y') . '-' . rand(100, 999);

        $payload = [
            'id' => $id,
            'season' => $request->season,
            'brand_id' => $request->brandId,
            'style_code' => $request->styleCode,
            'category' => $request->category,
            'garment_l' => $request->garmentL,
            'garment_w' => $request->garmentW,
            'garment_h' => $request->garmentH,
            'compression_pct' => $request->compressionPct ?? 0,
            'lock_flat' => (bool) $request->lockFlat,
            'unit_weight_grams' => $request->unitWeightGrams,
            'order_qty' => $request->orderQty,
            'box_name' => $request->boxName,
            'box_l' => $request->boxL,
            'box_w' => $request->boxW,
            'box_h' => $request->boxH,
            'box_tare_grams' => $request->boxTareGrams,
            'max_weight_limit_kg' => $request->maxWeightLimitKg,
            'created_by' => $request->createdBy ?? 'Sistema',
            'updated_at' => now()->toIso8601String(),
        ];

        $this->supabase(['Prefer' => 'resolution=merge-duplicates'])
            ->post('/mcq_tests', $payload);

        return response()->json(['status' => 'ok', 'id' => $id]);
    }

    public function deleteTest(string $id)
    {
        $this->supabase()->delete('/mcq_tests?id=eq.' . urlencode($id));
        return response()->json(['status' => 'deleted']);
    }

    public function addSeason(Request $request)
    {
        $code = strtoupper(trim($request->code));
        $this->supabase(['Prefer' => 'resolution=ignore-duplicates'])
            ->post('/seasons', ['code' => $code]);

        return response()->json(['status' => 'ok', 'code' => $code]);
    }

    public function addBrand(Request $request)
    {
        $this->supabase()->post('/brands', [
            'id' => $request->id,
            'name' => $request->name,
            'badge' => strtoupper($request->badge),
            'color' => $request->color,
        ]);
        return response()->json(['status' => 'ok']);
    }

    public function addBox(Request $request)
    {
        $id = 'box-' . time();
        $this->supabase()->post('/master_boxes', [
            'id' => $id,
            'brand_id' => $request->brandId,
            'name' => $request->name,
            'l' => $request->l,
            'w' => $request->w,
            'h' => $request->h,
            'tare' => $request->tare,
            'max_kg' => $request->maxKg,
        ]);
        return response()->json(['status' => 'ok', 'id' => $id]);
    }

    public function addUser(Request $request)
    {
        $this->supabase()->post('/mcq_users', [
            'username' => strtolower(trim($request->username)),
            'name' => trim($request->name),
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);
        return response()->json(['status' => 'ok']);
    }

    public function deleteUser(string $username)
    {
        if ($username !== 'admin') {
            $this->supabase()->delete('/mcq_users?username=eq.' . urlencode($username));
        }
        return response()->json(['status' => 'deleted']);
    }
    
    public function deleteBox(string $id)
    {
        $this->supabase()->delete('/master_boxes?id=eq.' . urlencode($id));
        return response()->json(['status' => 'deleted']);
    }

    public function deleteSeason(string $code)
    {
        $this->supabase()->delete('/seasons?code=eq.' . urlencode($code));
        return response()->json(['status' => 'deleted']);
    }

    public function deleteBrand(string $id)
    {
        // Al tener borrado en cascada en las tablas relacionales, eliminar la marca limpia sus cajas y tests asociados
        $this->supabase()->delete('/brands?id=eq.' . urlencode($id));
        return response()->json(['status' => 'deleted']);
    }
}