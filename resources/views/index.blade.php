<script data-exec-on-popstate>

    $(function () {
        var timer;
        $('.filter-routes').on('keyup', function () {
            var _this = this;
            clearTimeout(timer);
            timer = setTimeout(function () {
                var search = $(_this).val();
                var regex = new RegExp(search);
                $('ul.routes li').each(function () {
                    if (!regex.test($(this).data('uri'))) {
                        $(this).addClass('hide');
                    } else {
                        $(this).removeClass('hide');
                    }
                });
            }, 300);
        });

        $('.route-item a').click(function () {

            var li = $(this).parent('li');

            $('a.method').html(li.data('method')).removeClass(function (index, className) {
                return (className.match(/bg-[^\s]+/) || []).join(' ');
            }).addClass('bg-'+li.data('method-color'));

            $('.uri').val(li.data('uri'));
            $('input.method').val(li.data('method'));

            $('.param').remove();

            $('.response-tabs').addClass('hide');

            appendParameters(li.data('parameters'));
        });

        function getParamCount() {
            return $('.param').length;
        }

        function appendParameters(params) {

            for (var param in params) {

                var html = $('template.param-tpl').html();
                html = html.replace(new RegExp('__index__', 'g'), getParamCount());

                var append = $(html);
                append.find('.param-key').val(params[param].name);
                append.find('.param-val').val(params[param].defaultValue);
                append.find('.param-desc').removeClass('hide').find('.text').html(params[param].description);

                if (params[param].required == 'true') {
                    append.find('.param-desc .param-required').removeClass('hide');
                }

                if (params[param].type == 'file') {
                    append.find('.param-val').attr('type', 'file');
                    append.find('.change-val-type i').toggleClass("fa-upload fa-pencil");
                }

                $('.param-add').before(append);
            }
        }

        $('.params').on('click', '.change-val-type', function () {
            var type = $(this).parent().prev().attr('type') == 'text' ? 'file' : 'text';
            $(this).parent().prev().attr('type', type);

            $("i", this).toggleClass("fa-upload fa-pencil");
        }).on('click', '.param-remove', function () {
            $(this).closest('.param').remove();
        });

        $('.param-add').on('focus', 'input', function () {
            var html = $('template.param-tpl').html();

            html = html.replace(new RegExp('__index__', 'g'), $('.param').length);

            $(this).closest('.param-add').before(html);

            $('.params .param').last().find('input:first').focus()
        });

        function renderResponse(response) {
            $('.response-tabs').removeClass('hide');

            $('.response-tabs #content pre code').html(response.content);
            $('.response-tabs #headers pre code').html(response.headers);
            $('.response-tabs #cookie pre code').html(response.cookies);

            $('.response-tabs pre code').removeClass(function (index, className) {
                return (className.match(/language-[^\s]+/) || []).join(' ');
            }).addClass('language-'+response.language);

            Prism.highlightAll();

            $('.response-status').html('status&nbsp;'+ response.status.code+'&nbsp;&nbsp;'+response.status.text);

            if (response.status.code >= 400) {
                $('.response-status').removeClass(function (index, className) {
                    return (className.match(/label-[^\s]+/) || []).join(' ');
                }).addClass('label-danger');
            } else {
                $('.response-status').removeClass(function (index, className) {
                    return (className.match(/label-[^\s]+/) || []).join(' ');
                }).addClass('label-success');
            }
        }

        $('.api-tester-form').on('submit', function (event) {

            event.preventDefault();

            var formData = new FormData(this);

            if (formData.get('uri').length == 0) {
                return false;
            }

            $.ajax({
                method: 'POST',
                url: '{{ route('api-tester-handle') }}',
                data: formData,
                async: false,
                success: function (data) {
                    if (typeof data === 'object') {
                        if (data.status) {
                            toastr.success(data.message);
                            renderResponse(data.data);
                        } else {
                            toastr.error(data.message);
                        }
                    }
                },
                cache: false,
                contentType: false,
                processData: false
            });
        });

    });
</script>


<style>
    .param-add,.param {
        margin-bottom: 10px;
    }

    .param-add .form-group,.param .form-group {
        margin: 0;
    }

    .status-label {
        margin: 10px;
    }

    .response-tabs pre {
        border-radius: 0px;
    }

    .param-remove {
        margin-left: 5px !important;
    }

    .param-desc {
        display: block;
        margin-top: 5px;
        margin-bottom: 10px;
        color: #737373;
    }

    .nav-stacked>li {
        border-bottom: 1px solid #f4f4f4;
        margin: 0 !important;
    }

    .nav>li>a {
        padding: 10px 10px;
    }

    </style>

<div class="row">
    <div class="col-md-3">

        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="false">Routes</a></li>
                {{--<li><a href="#tab_2" data-toggle="tab" aria-expanded="true">History</a></li>--}}
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab_1">

                <form action="#" method="post">
                    <div class="input-group">
                        <input type="text" name="message" placeholder="Type Url ..." class="form-control filter-routes">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-primary btn-flat"><i class="fa fa-search"></i></button>
                        </span>
                    </div>
                </form>

                <ul class="nav nav-pills nav-stacked routes" style="margin-top: 5px;">
                    @foreach($routes as $route)
                        @php ($color = Encore\Admin\ApiTester\ApiTester::$methodColors[$route['method']])
                        <li class="route-item"
                            data-uri="{{ $route['uri'] }}"
                            data-method="{{ $route['method'] }}"
                            data-method-color="{{$color}}"
                            data-parameters='{!! $route['parameters'] !!}' >

                            <a href="#"><b>{{ $route['uri'] }}</b>
                                <div class="pull-right">
                                    <span class="label bg-{{ $color }}">{{ $route['method'] }}</span>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>

                </div>
                <!-- /.tab-pane -->
                {{--<div class="tab-pane" id="tab_2">--}}
                    {{--<ul class="nav nav-pills nav-stacked routes" style="margin-top: 5px;">--}}
                        {{--@foreach($logs as $route)--}}
                            {{--@php ($color = Encore\Admin\ApiTester\ApiTester::$methodColors[$route['method']])--}}
                            {{--<li class="route-item"--}}
                                {{--data-uri="{{ $route['uri'] }}"--}}
                                {{--data-method="{{ $route['method'] }}"--}}
                                {{--data-method-color="{{$color}}"--}}
                                {{--data-parameters='{!! $route['parameters'] !!}' >--}}

                                {{--<a href="#"><b>{{ $route['uri'] }}</b>--}}
                                    {{--<div class="pull-right">--}}
                                        {{--<span class="label bg-{{ $color }}">{{ $route['method'] }}</span>--}}
                                    {{--</div>--}}
                                {{--</a>--}}
                            {{--</li>--}}
                        {{--@endforeach--}}
                    {{--</ul>--}}
                {{--</div>--}}
                <!-- /.tab-pane -->
            </div>
            <!-- /.tab-content -->
        </div>

    </div>

    <!-- /.col -->
    <div class="col-md-9">


        <div class="box box-info">
            <!-- /.box-header -->
            <!-- form start -->
            <form class="form-horizontal api-tester-form">
                <div class="box-body">
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">Request</label>

                        <div class="col-sm-10">
                            <div class="input-group">
                                <span class="input-group-btn">
                                    <a class="btn bg-gray btn-flat method">method</a>
                                </span>
                                <input type="text" name="uri" class="form-control uri">
                                <input type="hidden" name="method" class="form-control method">
                                {{ csrf_field() }}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputUser" class="col-sm-2 control-label">Login as</label>

                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="inputUser" name="user" placeholder="Enter a user id or token to login with specific user.">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Parameters</label>

                        <div class="col-sm-10">
                            <div class="params">
                                <div class="form-inline param-add">
                                    <div class="form-group"> <!-- Username field -->
                                        <input type="text" class="form-control" style="width: 120px" placeholder="key"/>
                                    </div>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input type="text" class="form-control" style="width: 280px"  placeholder="value"/>
                                    <span class="input-group-btn">
                                      <a type="button" class="btn btn-default btn-flat change-val-type"><i class="fa fa-upload"></i></a>
                                    </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0px;">
                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-primary">Send</button>
                        </div>
                    </div>

                </div>

            </form>
        </div>

        <div class="nav-tabs-custom response-tabs hide">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#content" data-toggle="tab">Content</a></li>
                <li><a href="#headers" data-toggle="tab">Headers</a></li>
                {{--<li><a href="#cookies" data-toggle="tab">Cookies</a></li>--}}
                <li class="status-label"><span class="label label-default response-status"></span></li>
            </ul>
            <div class="tab-content">

                <div class="active tab-pane" id="content">
                    <div class="form-group"><pre><code class="line-numbers"></code></pre></div>
                </div>
                <!-- /.tab-pane -->
                <div class="tab-pane" id="cookies">
                    <div class="form-group"><pre><code class="line-numbers"></code></pre></div>
                </div>

                <div class="tab-pane" id="headers">
                    <div class="form-group"><pre><code class="line-numbers"></code></pre></div>
                </div>
                <!-- /.tab-pane -->
            </div>
            <!-- /.tab-content -->
        </div>

    </div>
    <!-- /.col -->
</div>


<template class="param-tpl">

    <div class="form-inline param">

        <div class="form-group"> <!-- Username field -->
            <input type="text" name="key[__index__]" class="form-control param-key" style="width: 120px" placeholder="Key"/>
        </div>
        <div class="form-group">
            <div class="input-group">
                <input type="text" name="val[__index__]" class="form-control param-val" style="width: 280px"  placeholder="value"/>
                <span class="input-group-btn">
                  <a type="button" class="btn btn-default btn-flat change-val-type"><i class="fa fa-upload"></i></a>
                </span>
            </div>
        </div>

        <div class="form-group text-red">
            <i class="fa fa-times-circle param-remove"></i>
        </div>
        <br/>
        <div class="form-group param-desc hide">
            <i class="fa fa-info-circle"></i>&nbsp;
            <span class="text"></span>
            <b class="text-red hide param-required">*</b>
        </div>
    </div>

</template>