@component('mail::message')
# New Enquiry Received

You have received a new enquiry from your website.

**Name:** {{ $enquiry->name }}  
**Email:** {{ $enquiry->email }}  
**Phone:** {{ $enquiry->phone }}
**Service:** {{ $enquiry->service }}

**Message:**
> {{ $enquiry->message }}

Thanks,  
{{ config('app.name') }}
@endcomponent
