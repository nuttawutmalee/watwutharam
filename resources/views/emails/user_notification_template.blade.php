<p>Dear User,</p>

<p>Your message has been successfully sent, all information received will always remain confidential. <br>
    We will contact you as soon as we review your message.</p>

<p> Submission details </p>
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

<br/>

<p>Regards</p>

<br><p style="color:red">*** This is an auto-generated email. Please do not reply. ***</p>