<!DOCTYPE html>
<html>
<head>
    <title>Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        h1 {
            color: mediumvioletred;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            text-align: left;
        }
        th {
            background-color: mediumvioletred;
        }
        h3 {
            color: white;
        }
    </style>
</head>
<body>
<h1>Report</h1>
<p>Date of creating a report: {{ $reportGeneratedAt }}</p>
<p>Report period: from {{ $reportCreate }} per {{ $reportGeneratedAt }}</p>
<h2>Rating of methods call</h2>
<table>
    <thead>
    <tr>
        <th><h3>Method</h3></th>
        <th><h3>Number of calls</h3></th>
    </tr>
    </thead>
    <tbody>
    @foreach($method as $m)
        <tr>
            <td>{{ $m->controller_method }}</td>
            <td>{{ $m->total }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h2>Rating of changes of entities</h2>
<table>
    <thead>
    <tr>
        <th><h3>Entity</h3></th>
        <th><h3>Number of changes</h3></th>
    </tr>
    </thead>
    <tbody>
    @foreach($entity as $e)
        <tr>
            <td>{{ $e->entity }}</td>
            <td>{{ $e->total }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h2>Rating users requests</h2>
<table>
    <thead>
    <tr>
        <th><h3>User</h3></th>
        <th><h3>Number of requests</h3></th>
    </tr>
    </thead>
    <tbody>
    @foreach($userRequest as $ur)
        <tr>
            <td>{{ $ur->username }}</td>
            <td>{{ $ur->total }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h2>Rating of users by logins</h2>
<table>
    <thead>
    <tr>
        <th><h3>User</h3></th>
        <th><h3>Number of logins</h3></th>
    </tr>
    </thead>
    <tbody>
    @foreach($userLogin as $ul)
        <tr>
            <td>{{ $ul->username }}</td>
            <td>{{ $ul->total }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h2>Rating of users by permissions</h2>
<table>
    <thead>
    <tr>
        <th><h3>User</h3></th>
        <th><h3>Number of permissions</h3></th>
    </tr>
    </thead>
    <tbody>
    @foreach($userPermissions as $up)
        <tr>
            <td>{{ $up->username }}</td>
            <td>{{ $up->total_permissions }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h2>Rating of users by changes</h2>
<table>
    <thead>
    <tr>
        <th><h3>User</h3></th>
        <th><h3>Number of changes</h3></th>
    </tr>
    </thead>
    <tbody>
    @foreach($userChanges as $uc)
        <tr>
            <td>{{ $uc->username }}</td>
            <td>{{ $uc->total }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
