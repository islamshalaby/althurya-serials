@extends('admin.app')

@section('title' , __('messages.show_shops_orders'))

@section('content')
    <div id="tableSimple" class="col-lg-12 col-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-header">
            <div class="row">
                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                    <h4>{{ __('messages.show_shops_orders') }}</h4>
                </div>
            </div>
        </div>
        <div class="widget-content widget-content-area">
            <div class="table-responsive"> 
                <table id="html5-extension" class="table table-hover non-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Id</th>    
                            <th>{{ __('messages.name') }}</th>
                            <th>{{ __('messages.phone') }}</th>
                            <th>{{ __('messages.date') }}</th>
                            <th class="text-center">{{ __('messages.details') }}</th>
                            <th class="text-center">{{ __('messages.seen?') }}</th>                            
                            @if(Auth::user()->delete_data) 
                                <th class="text-center" >{{ __('messages.delete') }}</th>                            
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        @foreach ($data['sellers'] as $seller)
                            <tr class="{{$seller->seen == 0 ? 'unread' : '' }}" >
                                <td><?=$i;?></td>
                                <td>{{ $seller->name }}</td>
                                <td>{{ $seller->phone }}</td>
                                <td>{{ $seller->created_at }}</td>
                                <td class="text-center blue-color"><a href="{{ route('sellers.requests.details', $seller->id) }}" ><i class="far fa-eye"></i></a></td>
                                <td style="font-weight : bold" class="text-center blue-color" >
                                    {{ $seller->seen == 0 ?  __('messages.unseen')  :  __('messages.seen')  }}
                                </td>
                                @if(Auth::user()->delete_data) 
                                    <td class="text-center blue-color" ><a onclick='return confirm("{{ __('messages.are_you_sure') }}");' href="/admin-panel/contact_us/delete/{{ $seller->id }}" ><i class="far fa-trash-alt"></i></a></td>                                
                                @endif
                                <?php $i++; ?>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        {{-- <div class="paginating-container pagination-solid">
            <ul class="pagination">
                <li class="prev"><a href="{{$data['contact_us']->previousPageUrl()}}">Prev</a></li>
                @for($i = 1 ; $i <= $data['contact_us']->lastPage(); $i++ )
                    <li class="{{ $data['contact_us']->currentPage() == $i ? "active" : '' }}"><a href="/admin-panel/contact_us/?page={{$i}}">{{$i}}</a></li>               
                @endfor
                <li class="next"><a href="{{$data['contact_us']->nextPageUrl()}}">Next</a></li>
            </ul>
        </div>   --}}
        
    </div>  

@endsection