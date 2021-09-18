@extends('front.app')
@section('title', 'تفاصيل الطلب')
@section('content')
<main id="main">

    <!-- =======  Order Details ======= -->
    <section class="OrderDetails section-t8 p-top">
        <div class="container">
            <div class="title-wrap d-flex justify-content-between">
                <div class="title-box">
                    <h2 class="title-a">تفاصيل الطلب</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 col-md-7 col-12">
                    <div class="card p-2">
                        @if (count($data['order']->items) > 0)
                        @foreach ($data['order']->items as $item)
                        <div class="DetailsBox">
                            <div class="imgCart">
                                <img src="https://res.cloudinary.com/al-thuraya/image/upload/w_144,h_209,q_100/v1581928924/{{ $item->image }}" class="img-fluid rounded-start" alt="...">
                            </div>
                            <div class="textCart">
                                <div class="card-body">
                                    <h3 class="p-price">{{ $item->final_price }}  {{ $currency_data['currency']->currency_ar }}</h3>
                                    <h6>{{ $item->category_name }}</h6>
                                    <h2 class="p-title">{{ $item->title }}</h2>
                                    @if ($item->discount > 0)
                                    <h3 class="Discount">
                                        <font>{{ $item->price_before_offer }}  {{ $currency_data['currency']->currency_ar }}</font> <span class="DiscountBox">{{ $item->discount }}%</span>
                                    </h3>
                                    @endif
                                    
                                    <p class="quantityText"><span>الكمية  | {{ $item->count }}</span> </p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @endif
                    </div>
                </div>
                <div class="col-lg-4 col-md-5 col-12">
                    <div class="card DetailsPriceBox">
                        <div class="card-header">
                            <div class=" d-flex justify-content-between">
                                <p class="card-text">رقم الطلب</p>
                                <h6 class="card-text">{{ $data['order']->order_number }}</h6>
                            </div>
                            <div class=" d-flex justify-content-between">
                                <p class="card-text">
                                    تاريخ الطلب
                                </p>
                                <h6 class="card-text">{{ $data['order']->date }}</h6>
                            </div>
                        </div>
                        <div class="card-body m-3 p-0">
                            {{--  <div class=" d-flex justify-content-between">
                                <p class="card-text">Delivery</p>
                                <p class="card-text">Free </p>
                            </div>  --}}
                            <div class=" d-flex justify-content-between">
                                <h4 class="card-text">الإجمالى</h4>
                                <h4 class="card-text">{{ $data['order']->total_price }}  {{ $currency_data['currency']->currency_ar }}</h4>
                            </div>

                        </div>

                        <div class="PaymentMethod m-3"><i class="bi bi-credit-card-2-front"></i>ماى فاتورة</div>

                    </div>
                </div>

            </div>
        </div>
    </section>
    <!-- ======= Latest products Section ======= -->


</main>
<!-- End #main -->
@endsection