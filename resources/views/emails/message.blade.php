<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ env('APP_NAME') }}</title>

    <style>
        a {
            text-decoration: none;
            color: #000;
        }

        body {
            background: rgb(218, 165, 32, .1);
        }

        .document {
            text-align: center;
            margin-top: 20px;
        }

        iframe {
            width: 1000px;
            height: 700px;
        }

        .header {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .logo a {
            text-align: center;
            font-size: .8em;
            font-weight: bold;
            width: 160px;
            text-transform: uppercase;
            text-align: center;
            color: goldenrod;
        }

        .btn {
            margin-bottom: 10px;
            padding: 5px 15px;
            border: 1px solid rgb(218, 165, 32, .6);
            background-color: #fff;
            border-radius: 5px;
            cursor: pointer;
        }

        .footer {
            display: flex;
            justify-content: space-around;
        }

        table {
            width:100%;
        }

        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
        }

        #t01 tr:nth-child(even) {
            background-color: #eee;
        }

        #t01 tr:nth-child(odd) {
            background-color: #fff;
        }

        #t01 th {
            background-color: rgb(218, 165, 32, .9);
            color: #000;
        }

    </style>

</head>
<body>
    <div class="email-template">
        <div class="header">
            <div class="logo">
                <a href="https://tds.gov.tm/tm">
                    <img src="https://tds.gov.tm/img/tds-logo/tds-logo.webp" alt="https://tds.gov.tm/img/tds-logo/tds-logo.webp" width="100px">
                </a>
            </div>
            <div class="logo">
                <a href="https://tds.gov.tm/tm">
                    <p>«Türkmenstandartlary» <br> Baş Döwlet Gullugy</p>
                </a>
            </div>
        </div>

        <div class="document">
            <div>
                <table id="t01">
                    <thead>
                        <tr>
                            <th>{{ __('Username') }}</th>
                            <th>{{ __('Phone number') }}</th>
                            <th>{{ __('Email Address') }}</th>
                            <th>{{ __('Message') }}</th>
                            <th>{{ __('Created time') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $message->username }}</td>
                            <td>{{ $message->phone_number }}</td>
                            <td>{{ $message->email }}</td>
                            <td>{{ $message->message }}</td>
                            <td>{{ \Carbon::parse($message->created_at)->locale(config('app.faker_locales.' . app()->getlocale() ))->isoFormat('LLLL') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="document">
            <div>
                <table id="t01">
                    <thead>
                        <tr>
                            <th>{{ __('First Name') }}</th>
                            <th>{{ __('Last Name') }}</th>
                            <th>{{ __('Email Address') }}</th>
                            <th>{{ __('Phone number') }}</th>
                            <th>{{ __('Address') }}</th>
                            <th>{{ __('Company name') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $user->first_name }}</td>
                            <td>{{ $user->last_name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone_number }}</td>
                            <td>{{ $user->address }}</td>
                            <td>{{ $user->company_name }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>
</body>
</html>
