<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Daewoo Voucher</title>

<style>
  body {
    font-family: Arial, sans-serif;
    margin: 20px;
  }

  .header {
    text-align: center;
    font-weight: bold;
    font-size: 24px;
  }

  .sub-header {
    text-align: center;
    font-size: 25px;
    font-weight: 700;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
  }

  .info-lines div {
    font-size: 14px;
    margin-bottom: 6px;
  }

  .line-between {
    display: flex;
    justify-content: space-between;
  }

  .line-multi {
    display: flex;
    justify-content: space-between;
    gap: 10px;
  }
.text-align{
    text-align: right;
}
  .data-table th,
  .data-table td {
    border-bottom: 1px solid #000;
    padding: 6px;
    text-align: left;
  }

  .data-table th {
    font-weight: bold;
  }

  /* Right side summary (Online/Balance) */
.right-summary {
   
    width: 200px;
    font-weight: bold;
}
.summary-line {
    display: flex;
    justify-content: space-between;
    padding: 2px 0;
}
.summary-line.online {
    border-top: 1px dashed #000;
}
.summary-line.balance {
    border-top: 2px solid #000;
    font-size: 14px;
}
.date-time {
    font-size: 9px;
    font-style: italic;
    text-align: right;
    margin-top: 5px;
}

/* --- GOJ/LHR/36 Box Positioning --- */

.lhr-box {
    border: 1px solid #000;
    width: 50px;
    height: 20px;
    text-align: center;
    font-weight: bold;
    line-height: 20px;
    margin-left: 25px; 
}
.goj-container {
    display: flex;
    margin-top: -1px;
}
.goj-box, .goj-value {
    border: 1px solid #000;
    width: 25px;
    height: 20px;
    text-align: center;
    font-weight: bold;
    line-height: 20px;
}
.goj-box { border-right: none; }
.goj-value { width: 50px; }

.route-summary{
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 10px;
}

/* --- Footer Breakdown Table --- */
.footer-table-container {
    border: 1px solid #000;
    padding: 5px;
    margin-top: 30px;
    width: 500px;
    align-self: flex-start;
}
.footer-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.footer-table th, .footer-table td { padding: 4px 6px; text-align: right; border: 1px solid #000; font-weight: bold; }
.footer-table thead th { font-weight: bold; text-align: center; }
.dest-cell { text-align: left !important; width: 60px; }
.total-col-header, .total-col-data { width: 15%; }
.final-total td { border-bottom: 2px solid #000; }
</style>
</head>

<body>
<div>

  <div class="header">B. S</div>
  <div class="sub-header">Daewoo Bus Service</div>

 <div class="info-lines">

  <div class="line-between">
    <span><strong>Route:</strong> GOJ-LHR <span>5:30 AM</span></span>
    <span><strong>Date:</strong> 2025-11-03</span>
  </div>

  <div class="line-multi">
    <span><strong>Vehicle No:</strong> 5137 CAB 20</span>
    <span><strong>Arrival Time:</strong> 08:00 AM</span>
    <span><strong>Departure Time:</strong> 08:30 AM</span>
    <span><strong>Voucher No:</strong> 227,909</span>
  </div>

  <div class="line-between">
    <span><strong>Driver:</strong> DRIVER</span>
    <span><strong>Host:</strong> HOST</span>
  </div>

</div>

<br>

<table class="data-table">
  <tr>
    <th>Seat</th>
    <th>Name</th>
    <th>CNIC</th>
    <th>Cell</th>
    <th>Via</th>
    <th>By</th>
    <th>Desti.</th>
    <th>Fare</th>
  </tr>

  <tr><td>2</td><td>RASHID</td><td>333019273293</td><td>923000000000</td><td>C</td><td>ADNAN</td><td>LHR</td><td>1,250</td></tr>
  <tr><td>4</td><td>ADNAN</td><td>3330155386277</td><td>923000000000</td><td>C</td><td>ADNAN</td><td>LHR</td><td>1,250</td></tr>
</table>

<br><br>

<div style="font-size:14px; font-weight:bold; border-top:2px solid #000; padding-top:10px;  border-bottom: 1px solid #000;">

  <table style="width:100%; font-size:14px; font-weight:bold; ">
    <tr>
      <td class="text-align">Printed By:</td>
      <td>ADMIN</td>
      <td class="text-align">Total Pax:</td>
      <td>36</td>
      <td class="text-align">Total Fare:</td>
      <td>45,289</td>
      
    </tr>
  </table>

  <br>
  <table style="width:100%; font-size:14px; font-weight:bold; border-top: 1px solid #000;">
   <tr>
     <td class="text-align">Other Income:</td>
      <td>0</td>
   </tr>
  </table>

 
<br>
</div>

<table style="width:100%;  font-size:14px; font-weight:bold; margin:1rem 0;  border-bottom: 1px solid #000;">
    <tr style="padding-top: 10px;">
      <td class="text-align">Adda:</td>
      <td>3,000</td>
      <td class="text-align">Hakri:</td>
      <td>200</td>
      <td class="text-align">Others:</td>
      <td>90</td>
      <td class="text-align">Total Expense:</td>
      <td>3,290</td>
    </tr>
  </table>


<div class="route-summary">
     <div class="goj-lhr-box">
        <div class="lhr-box">LHR</div>
        <div class="goj-container">
            <div class="goj-box">GOJ</div>
            <div class="goj-value"><strong>36</strong></div>
        </div>
    </div>


    <div class="right-summary">
        <div class="summary-line online">
            <span>Online</span>
            <span>9,039</span>
        </div>
        <div class="summary-line balance">
            <span>Balance</span>
            <span>32,960</span>
        </div>
        <div class="date-time">6-Nov-2025 9:20 am</div>
    </div>
</div>
 


<div class="footer-table-container">
        <table class="footer-table">
            <thead>
                <tr>
                    <td></td>
                    <th>AHMAD</th>
                    <th>ADNAN</th>
                    <th>ALYAN SAEED</th>
                    <th>ASAD</th>
                    <th class="total-col-header">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="dest-cell">LHR</td>
                    <td>11,250</td>
                    <td>15,000</td>
                    <td>8,750</td>
                    <td>1,250</td>
                    <td class="total-col-data">36,250</td>
                </tr>
                <tr class="final-total">
                    <td class="dest-cell">Total</td>
                    <td>11,250</td>
                    <td>15,000</td>
                    <td>8,750</td>
                    <td>1,250</td>
                    <td class="total-col-data">36,250</td>
                </tr>
            </tbody>
        </table>
    </div>
  
</div>


</body>
</html>