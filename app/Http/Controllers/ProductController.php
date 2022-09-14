<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateProductRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUser;
use Illuminate\Http\Request;
use App\Filters\ProductFilter;
use App\Http\Resources\PaymentTypeResource;
use App\Http\Resources\ProductAdditionalsResource;
use App\Http\Resources\ProductChartsResource;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductTeamsResource;
use App\Http\Resources\ProductUsersResource;
use App\Models\Chart;
use App\Models\Graph;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Price;
use App\Models\ProductBonus;
use App\Models\Reason;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use App\Models\NextPrice;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    private $root;
    private $perPage;

    public function __construct()
    {
        $this->root = 'products';
        $this->perPage = 45;
    }

    public function getList($type, ProductFilter $filters)
    {
        access(['can-head', 'can-host']);

        $query = Product::query();
        $products = $query->latest()->where('category', $type)->filter($filters)->paginate($this->perPage)->appends(request()->all());

        return response()->json(new ProductCollection($products), 200);
    }

    public function getFilters()
    {
        access(['can-head', 'can-host']);

        $data['main'] = [
            [
                'name' => 'title',
                'title' => 'Заголовок',
                'type' => 'input',
            ],
        ];

        return response()->json($data, 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        access(['can-head', 'can-host']);

        return view("{$this->root}.index");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access(['can-head', 'can-host']);
        $paymentTypes = PaymentType::whereIsActive(true)->get()->pluck('title', 'name')->toArray();
        $users = User::all()->pluck('account', 'id')->toArray();
        $teams = Team::all()->pluck('name', 'id')->toArray();
        $charts = Chart::all()->pluck('title', 'id')->toArray();
        $additionals = Product::get()->pluck('title', 'id')->toArray();

        return view("{$this->root}.create", [
            'paymentTypes' => $paymentTypes,
            'additionals' => $additionals,
            'users' => $users,
            'teams' => $teams,
            'charts' => $charts,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CreateProductRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateProductRequest $request, Product $product)
    {
        access(['can-head', 'can-host']);
        $this->updateOrCreate($request->all(), $product, 'create');

        return redirect()->route("{$this->root}.index")->with('success', 'Продукт успешно создан.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        access(['can-head', 'can-host']);

        return view("{$this->root}.show", [
            'product' => $product,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        access(['can-head', 'can-host']);
        $productPrices = $product->prices()->pluck('price');
        $nextPrice = $product->nextPrice()->latest()->first();
        $productTeams = $product->teams;
        $productCharts = $product->charts;
        $productAdditionals = $product->additionals;
        $reasons = $product->reasons()->where('is_active', true)->pluck('title');
        $users = User::where('is_active','>','0')->get()->pluck('name', 'id')->toArray();
        $teams = Team::all()->pluck('name', 'id')->toArray();
        $charts = Chart::all()->pluck('title', 'id')->toArray();
        $productPaymentTypes = $product->paymentTypes;
        $paymentTypes = PaymentType::whereIsActive(true)->get()->pluck('title', 'name')->toArray();
        $additionals = Product::where('id', '!=', $product->id)->get()->pluck('title', 'id')->toArray();
       // $productUsers = ProductUser::where('product_id', $product->id)->get();
        $productUsers = $product->users;


        return view("{$this->root}.edit", [
            'product' => $product,
            'additionals' => $additionals,
            'productPrices' => $productPrices,
            'nextPrice' => $nextPrice->price ?? null,
            'period' => $nextPrice->period ?? null,
            'month'=> array(1, 2, 3),
            'reasons' => $reasons,
            'category' => ProductCategory::where('id', $product->category)->first(),
            'paymentTypes' => $paymentTypes,
            'productPaymentTypes' => PaymentTypeResource::collection($productPaymentTypes),
            'productAdditionals' => ProductAdditionalsResource::collection($productAdditionals),
            'productTeams' => ProductTeamsResource::collection($productTeams),
            'productCharts' => ProductChartsResource::collection($productCharts),
            'productUsers' => ProductUsersResource::collection($productUsers),
            'teams' => $teams,
            'users' => $users,
            'charts' => $charts,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  CreateProductRequest $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(CreateProductRequest $request, Product $product)
    {
        access(['can-head', 'can-host']);

//        if( $request->trial_period < 1){
//            return redirect()->to(route("{$this->root}.index"))->with('success', 'Триал период не может быть меньше 1 дня!');
//        }
//        else{
            $this->updateOrCreate($request->all(), $product, 'update');
  //      }

        $message = 'Данные продукта успешно изменены.';
        if ($request->ajax()) {
            return response()->json([
                'message' => $message,
            ]);
        } else {
            return redirect()->to(route("{$this->root}.index"))->with('success', $message);
        }
    }

    private function updateOrCreate(array $request, ?Product $product, $type)
    {
        $priceIds = [];
        $reasonIds = [];
        $chartIds = [];
        $paymentTypeIds = [];
        $nextPrice = $request['next-price'];
        $period= $nextPrice->period ?? null;
        $prices = $request['prices'] ?? [];
        $productUsers = $request['productUsers'] ?? [];
        $productTeams = $request['productTeams'] ?? [];
        $paymentTypes = $request['paymentTypes'] ?? [];
        $productReasons = $request['reasons'] ?? [];
        $productCharts = $request['productCharts'] ?? [];
        $productAdditionals = $request['productAdditionals'] ?? [];

        if ($type == 'update') {
              $product->update($request);
//            $nextPrice = NextPrice::where('product_id', $product->id)
//                ->update(['price' => $nextPrice]);

        } else if ($type == 'create') {
            $product = $product->create($request);

        }

        foreach ($prices as $item) {
            if ($item) {
                $price = Price::updateOrCreate([
                    'price' => $item,
                    'product_id' => $product->id,
                ]);
                $priceIds[] = $price->id;
            }
        }

        if($nextPrice){
            $nextPrice  = NextPrice::updateOrCreate(
                [ 'product_id' => $product->id],
                [ 'price' => $nextPrice ],
                [ 'period' => $period ]
            );
        }
        if($period){
            $nextPrice  = NextPrice::updateOrCreate(
                [ 'product_id' => $product->id],
                [ 'period' => $period ]
            );
        }


        foreach ($productReasons as $reason) {
            $productReason = Reason::updateOrCreate([
                'title' => $reason,
                'product_id' => $product->id,
            ], [
                'is_active',
                'title' => $reason,
                'product_id' => $product->id,
            ]);
            $reasonIds[] = $productReason->id;
        }

        $product->paymentTypes()->detach();
        $bonusIds = [];
        foreach ($paymentTypes as $item) {
            $paymentType = PaymentType::whereName($item['type'])->firstOrFail();
            $product->paymentTypes()->attach([$paymentType->id]);
            if (isset($item['bonuses'])) {
                foreach ($item['bonuses'] as $type => $amount) {
                    $bonus = ProductBonus::updateOrCreate([
                        'product_id' => $product->id,
                        'payment_type_id' => $paymentType->id,
                        'type' => $type,
                        'amount' => $amount,
                    ], [
                        'product_id' => $product->id,
                        'payment_type_id' => $paymentType->id,
                        'type' => $type,
                        'is_active' => true,
                        'amount' => $amount,
                    ]);

                    $bonusIds[] = $bonus->id;
                }
            }
        }

        ProductBonus::where('product_id' ,$product->id)->whereNotIn('id', $bonusIds)->update([
            'is_active' => false,
        ]);

        $product->prices()->whereNotIn('id', $priceIds)->delete();
        $product->reasons()->whereNotIn('id', $reasonIds)->update([
            'is_active' => false,
        ]);
         $product->users()->detach();

         foreach ($productUsers as $productUser) {
             $product->users()->attach([
                 $productUser['id'] => [
                     'stake' => 0
                 ],
             ]);
         }

        $product->teams()->detach();

        foreach ($productTeams as $productTeam) {
            $product->teams()->attach([
                $productTeam['id'],
            ]);
        }

        $product->additionals()->detach();

        foreach ($productAdditionals as $productAdditional) {
            $product->additionals()->attach([
                $productAdditional['id'],
            ]);
        }

        $product->charts()->detach();
        $product->graphs()->detach();
        foreach ($productCharts as $productChart) {
            $product->charts()->attach([
                $productChart['id'],
            ]);

            $graphs = Graph::whereChartId($productChart['id'])->get();

            foreach ($graphs as $graph) {
                $product->graphs()->attach([
                    $graph->id,
                ]);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        // access(['can-head', 'can-host']);

        // $product->delete();
        // return redirect()->route("{$this->root}.index")->with('success', 'Продукт успешно удален.');
    }

//    public function withPrices()
//    {
//        access(['can-head', 'can-host', 'can-operator']);
//
//        $products = Product::get();
//        $data = [];
//        foreach ($products as $product) {
//            $data[$product->id] = [
//                'title' => $product->title,
//                'prices' => [],
//            ];
//            if (count($product->prices) > 0) {
//                $prices = [];
//                foreach ($product->prices as $price) {
//                    $prices[$price->id] = $price->price;
//                }
//                $data[$product->id]['prices'] = $prices;
//            }
//        }
//
//        return response()->json($data, 200);
//    }

    public function withPrices()
    {
        access(['can-head', 'can-host', 'can-operator']);

        $userId = Auth::id();

        $products = Product::get();
        $data = [];
        foreach ($products as $product) {

//            if (count($product->users) > 0) {
//                $users = [];
//                foreach ($product->users as $items) {
//                    if($items->id == $userId){

                        $data[$product->id] = [
                            'id' => $product->id,
                            'title' => $product->title,
                            'category' => $product->category,
                            'block_amount' => $product->block_amount,
                            'trial_period' => $product->trial_period,
                            'prices' => [],
                            // 'product_users' => $items,
                        ];

                        if (count($product->prices) > 0) {
                            $prices = [];
                            foreach ($product->prices as $price) {
                                $prices[$price->id] = $price->price;
                            }
                            $data[$product->id]['prices'] = $prices;
                        }
//                    }
//                }
//
//            }


        }

        return response()->json($data, 200);
    }

    public function archiveProduct(Request $request)
    {
        $today  = Carbon::now();
        $update = Product::where('id',$request->id)
               ->update(['archived' => 1]);
        if ($update) {
            return response()->json('success', 200);
        }
        else {
            return response()->json('error', 500);
        }
    }

    public function restoreProduct(Request $request)
    {

        $update = $affected = \DB::table('products')
            ->where('id', $request->id)
            ->update(['archived' => null]);

        if ($update) {
            return response()->json('success', 200);
        }
        else {
            return response()->json('error', 500);
        }
    }
}
