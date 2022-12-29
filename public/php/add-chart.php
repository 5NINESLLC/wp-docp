<html>

<head>
  <!--Load the AJAX API-->
  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  <script type="text/javascript">
    // Load the Visualization API and the corechart package.
    google.charts.load('current', {
      'packages': ['corechart']
    });
    //      google.charts.load('current', { packages:['corechart'], callback: drawChart });

    // Set a callback to run when the Google Visualization API is loaded.
    google.charts.setOnLoadCallback(drawChart);

    // Callback that creates and populates a data table,
    // instantiates the pie chart, passes in the data and
    // draws it.
    function drawChart() {

      // Create the data table.
      var data = new google.visualization.DataTable();
      data.addColumn('string', 'Topping');
      data.addColumn('number', 'Slices');
      data.addRows([
        ['Mushrooms', 3],
        ['Onions', 1],
        ['Olives', 1],
        ['Zucchini', 1],
        ['Pepperoni', 2]
      ]);

      // Set chart options
      var pie_options = {
        'title': 'How Much Pizza I Ate Last Night',
        'width': 400,
        'height': 300
      };
      var bar_options = {
        title: 'How Much Pizza I Ate Last Night',
        width: 400,
        height: 300,
        legend: 'none'
      };

      // Instantiate and draw our chart, passing in some options.
      var pieChart = new google.visualization.PieChart(document.getElementById('piechart_div'));
      pieChart.draw(data, pie_options);
      var barChart = new google.visualization.BarChart(document.getElementById('barchart_div'));
      barChart.draw(data, bar_options);
    }
  </script>
</head>

<body>
  <!--Div that will hold the pie chart-->
  <table>
    <tr>
      <td>
        <div id="piechart_div" style="border: 1px solid #ccc"></div>
      </td>
      <td>
        <div id="barchart_div" style="border: 1px solid #ccc"></div>
      </td>
    </tr>
  </table>


</body>

</html>