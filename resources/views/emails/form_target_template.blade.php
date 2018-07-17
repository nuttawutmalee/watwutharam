<strong>{{ $siteDomainName . ' - ' . $submissionName}}</strong><br/>
<em>Submitted at: {{ date('Y-m-d H:i:s T') }}</em><br/><br/><br/>
@if(isset($data) && ! empty($data))
    <table>
        @foreach($data as $value)
            <tr>
                <td><strong>{{ array_key_exists('label', $value) ? $value['label'] : (array_key_exists('name', $value) ? $value['name'] : null) }}</strong></td>
                @if (array_key_exists('value', $value))
                    @if(is_json($value['value']))
                        @if ($files = json_recursive_decode($value['value']))
                            <td>
                                <table>
                                    @foreach ($files as $file)
                                        <tr>
                                            <td></td>
                                            <td>{{ $file }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                        @endif
                    @else
                        <td>{{ $value['value'] }}</td>
                    @endif
                @else
                    <td></td>
                @endif
            </tr>
        @endforeach
    </table>
@endif

<br><p style="color:red">*** This is an auto-generated email. Please do not reply. ***</p>