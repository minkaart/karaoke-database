<script src="//code.jquery.com/jquery-1.10.2.js"></script>

<script>
$.getJSON( "test.json", function( json ) {
  console.log( "JSON Data: " + json.results[ 0 ] );
 });
</script>