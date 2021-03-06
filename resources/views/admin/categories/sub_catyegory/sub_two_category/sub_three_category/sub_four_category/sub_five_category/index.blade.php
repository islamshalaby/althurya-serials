@extends('admin.app')
@section('title' , __('messages.sub_category_fiveth'))
@section('content')
    <div id="tableSimple" class="col-lg-12 col-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-header">
                <div class="row">
                    <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                        <h4>{{ __('messages.sub_category_fiveth') }}</h4>
                    </div>
                </div>
                <div class="row">
                    @if(Auth::user()->add_data)
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                            <a class="btn btn-primary" href="{{route('sub_five_cat.create.new',$cat_id)}}">{{ __('messages.add') }}</a>
                        </div>
                    @endif
                </div>
            </div>
            <div class="widget-content widget-content-area">
                <div class="table-responsive">
                    <table id="html5-extension" class="table table-hover non-hover" style="width:100%">
                        <thead>
                        <tr>
                            <th class="text-center">Id</th>
                            <th class="text-center">{{ __('messages.image') }}</th>
                            <th class="text-center">{{ __('messages.name') }}</th>
                            @if(Auth::user()->update_data)<th class="text-center">{{ __('messages.edit') }}</th>@endif
                            @if(Auth::user()->delete_data)<th class="text-center" >{{ __('messages.delete') }}</th>@endif
                        </tr>
                        </thead>
                        <tbody>
                        <?php $i = 1; ?>
                        @foreach ($data as $row)
                            <tr >
                                <td class="text-center"><?=$i;?></td>
                                <td class="text-center"><img src="https://res.cloudinary.com/{{cloudinary_app_name()}}/image/upload/w_100,q_100/v1581928924/{{ $row->image }}"/></td>
                                <td class="text-center blue-color">{{ $row->title }}</td>

                                @if(Auth::user()->update_data)
                                    <td class="text-center blue-color" ><a href="{{ route( 'sub_five_cat.edit', $row->id ) }}" ><i class="far fa-edit"></i></a></td>
                                @endif
                                @if(Auth::user()->delete_data)
                                    <td class="text-center blue-color" >
                                        @if(count($row->products) > 0)
                                        {{ __('messages.category_has_products') }}
                                        @else
                                        <a onclick="return confirm('{{ __('messages.are_you_sure') }}');" href="{{ route('sub_five_cat.delete', $row->id) }}" >
                                            <i class="far fa-trash-alt"></i>
                                        </a>
                                        @endif
                                    </td>
                                @endif
                                <?php $i++; ?>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
@endsection


