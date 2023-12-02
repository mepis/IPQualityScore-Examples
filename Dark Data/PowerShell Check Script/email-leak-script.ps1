# Enter IPQS API Key between quotes below
$IPQS_API_KEY = ''

$CSV = Import-Csv -Path emails.csv
$results = New-Object -TypeName System.Collections.ArrayList

foreach($email in $CSV)
{
    $Uri = "https://www.ipqualityscore.com/api/json/leaked/email/$IPQS_API_KEY/" + $email.emails
    $response = Invoke-RestMethod -Uri $Uri -Method Get 
    $response | Add-Member -NotePropertyName email -NotePropertyValue $email.emails
    $response.source = $response.source -join " "
    $results.add($response)
}
$results | Export-Csv -Path "results.csv" -NoTypeInformation