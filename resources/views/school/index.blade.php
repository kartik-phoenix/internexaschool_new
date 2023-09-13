@extends('layouts.master')

@section('title')
    {{ __('create') . ' ' . __('school') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('School') }}
            </h3>
        </div>
        <div class="row grid-margin">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form id="frmData" class="general-setting" action="{{ route('masterschool.store') }}" method="POST" novalidate="novalidate" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-6 col-sm-12">
                                    <label>School Name</label>
                                    <input name="school_name" type="text" required placeholder="{{ __('School Name') }}" class="form-control"/>
                                </div>
                                <div class="form-group col-md-6 col-sm-12">
                                    <label>{{ __('status') }}</label>
                                    {{-- <input name="status" type="email" required placeholder="{{ __('status') }}" class="form-control"/> --}}
                                    <select required name="status" id="status" class="form-control select2 valid" style="width:100%;" tabindex="-1" aria-hidden="true" aria-invalid="false">
                                        <option value="1" selected>Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div> 
                                <div class="form-group col-md-6 col-sm-12">
                                    <label>{{ __('school_email') }}</label>
                                    <input name="school_email" type="email" required placeholder="{{ __('school_email') }}" class="form-control"/>
                                </div>
                            </div>
                            {{-- <div class="row">
                                <div class="form-group col-md-6 col-sm-12">
                                    <label>{{ __('first_name') }}</label>
                                    <input name="first_name" type="text" required placeholder="{{ __('first_name') }}" class="form-control"/>
                                </div>
                                <div class="form-group col-md-6 col-sm-12">
                                    <label>{{ __('last_name') }}</label>
                                    <input name="last_name" type="text" required placeholder="{{ __('last_name') }}" class="form-control"/>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6 col-sm-12">
                                    <label>{{ __('password') }}</label>
                                    <input name="password" type="password" required placeholder="{{ __('password') }}" class="form-control"/>
                                </div>
                            </div> --}}
                            <input class="btn btn-theme" type="submit" value="Submit">
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row grid-margin">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <table class='table overflow-auto table-bordered table-hover'>
                            <thead>
                                <tr>
                                    <th scope="col" data-visible="false">{{ __('id') }}</th>
                                    <th scope="col">{{ __('School Name') }}</th>
                                    <th scope="col">{{ __('Status') }}</th>
                                    <th scope="col">{{ __('School UID') }}</th>
                                    <th scope="col">{{ __('School URL') }}</th>
                                    <th scope="col">{{ __('Created Date') }}</th>
                                    <th data-events="actionEvents" scope="col" data-field="operate" data-sortable="false">{{ __('action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($schools as $school)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $school->school_name }}</td>
                                        <td>{{ ($school->status == 1) ?  "Active" : 'Inactive' }}</td>
                                        <td>{{ $school->school_uid }}</td>
                                        <td><a href="{{ $school->school_url }}" target="_blank">{{ substr($school->school_url, 0, 25) }}</a></td>
                                        <td>{{ $school->created_at }}</td>
                                        <td>  
                                            <select required name="status" id="status" onchange="schoolStatus(this.value, {{ $school->id }})" class="form-control select2 valid" style="width:100%;" tabindex="-1" aria-hidden="true" aria-invalid="false">
                                                <option value="1" {{($school->status == 1) ?  "selected" : '' }}>Active</option>
                                                <option value="0" {{($school->status == 0) ?  "selected" : '' }}>Inactive</option>
                                            </select>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="text-center">
                                        <td colspan="7">Record Not Found</td>
                                    </tr>
                                @endforelse
                                
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function schoolStatus(val, id) {
            let url = "{{ route('masterschool.update', ':id') }}";
            url = url.replace(':id', id);
            $.ajax({
                type: "put",
                url: url,
                data: {
                    "status" : val
                },
                dataType: "json",
                success: function (response) {
                    window.location.reload();

                }
            });
        }
    </script>
@endsection
