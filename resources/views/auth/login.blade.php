<!DOCTYPE html>
<html lang="en">

<x-head />

<body class="dark:bg-neutral-800 bg-neutral-100 dark:text-white">
    <section class="bg-white dark:bg-dark-2 flex flex-wrap min-h-[100vh]">
        <div class="lg:w-1/2 lg:block hidden">
            <div class="flex items-center flex-col justify-center bg-gradient-to-b from-slate-100 to-slate-300 dark:from-dark-2 dark:to-dark-3 h-full">
                <img src="{{ asset('assets/images/GuestFlow_logo.png') }}" alt="" style="max-height: 95%">
            </div>
        </div>
        <div class="lg:w-1/2 py-8 px-6 flex flex-col justify-center">
            <div class="lg:max-w-[464px] mx-auto w-full">
                <div>
                    <a href="{{ route('dashboard') }}" class="mb-2.5 max-w-[400px]">
                        <img src="{{ asset('assets/images/borgstena_logo.png') }}" alt="">
                    </a>
                    @if (isset($_SESSION['BITS']['id_employee']))
                    <h4 class="mb-3"><x-login-link key="{{$user->id}}" label="Hi, {{$_SESSION['BITS']['shortname']}}" class="linkLogin" /></h4>
                    <h4 class="mb-3">Please wait while processing automatic login</h4>

                    <div class="flex justify-center items-center mt-8">
                        <div class="jimu-primary-loading"></div>
                    </div>

                    @else
                    <h4 class="mb-3">Sign In to your Account</h4>
                    <p class="mb-8 text-secondary-light text-lg">Welcome back! please enter your detail</p>
                    @endif

                    @if ($errors->any())
                    <div class="mb-4 text-lg text-red-600">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                </div>

                @if (!isset($_SESSION['BITS']['id_employee']))
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="icon-field mb-4 relative">
                        <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                            <iconify-icon icon="mage:user"></iconify-icon>
                        </span>
                        <input type="text" id="login" name="login" value="{{old('login')}}" required autofocus autocomplete="username" class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 dark:bg-dark-2 rounded-xl" placeholder="Login or Email">
                    </div>
                    <div class="relative mb-5">
                        <div class="icon-field">
                            <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                                <iconify-icon icon="solar:lock-password-outline"></iconify-icon>
                            </span>
                            <input type="password" id="password" name="password" required autocomplete="current-password" class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 dark:bg-dark-2 rounded-xl" placeholder="Password">
                        </div>
                    </div>
                    {{-- <div class="mt-7">
                        <div class="flex justify-between gap-2">
                            <a href="javascript:void(0)" class="text-primary-600 font-medium hover:underline">Forgot Password?</a>
                        </div>
                    </div> --}}

                    <button type="submit" class="btn btn-primary justify-center text-sm btn-sm px-3 py-4 w-full rounded-xl mt-8"> Sign In</button>

                </form>
                @endif
            </div>
        </div>
    </section>

    <x-script />

    <script>
        $(document).ready(function() {
            setTimeout(function() {
                $('.linkLogin').click();
            }, 2000);
        });
    </script>

</body>

</html>