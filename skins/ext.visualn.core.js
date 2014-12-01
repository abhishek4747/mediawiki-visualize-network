



var width = 660,
  height = 400;

var color = d3.scale.category20();

var force = d3.layout.force()
    .charge(-120)
    .linkDistance(30)
    .size([width, height]);



function generateGraph(graph,max_depth) {
  $('#visualn svg').remove();
  var svg = d3.select("#visualn").append("svg")
  .attr("width", width)
  .attr("height", height);  

  max_depth = typeof max_depth !== 'undefined' ? max_depth : 100000;
  var graph_nodes = graph.nodes.filter(function (node){return node.depth <= max_depth});
  var graph_links = graph.links.filter(function (node){return node.depth <= max_depth});

  force
      .nodes(graph_nodes)
      .links(graph_links)
      .start();

  var link = svg.selectAll(".link")
      .data(graph_links)
    .enter().append("line")
      .attr("class", "link")
      .style("stroke-width", function(d) { return Math.sqrt(d.value); });

  link.append("title")
      .text(function(d) { return d.name; });

  var node = svg.selectAll(".node")
      .data(graph_nodes)
    .enter().append("circle")
      .attr("class", "node")
      .attr("r", function(d) { return 5*(d.depth==0?2:1); })
      .style("fill", function(d) { return color(d.depth); })
      .call(force.drag);  

  node.append("svg:text")
    .attr("x", 10)
    .attr("y", 15)
    .text(function(d) { return d.name; });

  node.append("title")
      .text(function(d) { return d.name; });

  force.on("tick", function() {
    link.attr("x1", function(d) { return d.source.x; })
        .attr("y1", function(d) { return d.source.y; })
        .attr("x2", function(d) { return d.target.x; })
        .attr("y2", function(d) { return d.target.y; });

    node.attr("cx", function(d) { return d.x; })
        .attr("cy", function(d) { return d.y; });
  });
}
$(window).load(function(){
  d3.select('#VN_slider').call(
    d3.slider().axis(true).min(1).max(10).step(1).value(8).on("slide", function(evt, value) {
      generateGraph(data, value);
    }));
});
