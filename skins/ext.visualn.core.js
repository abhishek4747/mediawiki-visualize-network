



var width = 660,
  height = 400;

var color = d3.scale.category20();

var force = d3.layout.force()
    .charge(-120)
    .linkDistance(30)
    .size([width, height]);



function generateGraph(graph,max_depth) {
  $('#visualnsvg svg').remove();
  var svg = d3.select("#visualnsvg").append("svg")
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
      .style("stroke-width", function(d) { return Math.sqrt(d.value); })
      .on("mouseout", function(d) {console.log("Edge:",d);});

  link.append("title")
      .text(function(d) { return d.name; });

  var node = svg.selectAll(".node")
      .data(graph_nodes)
    .enter().append("circle")
      .attr("class", "node")
      .attr("r", function(d) { return 5*(d.depth==0?2:1); })
      .style("fill", function(d) { return color(d.depth); })
      .on("mouseout", function(d) {console.log("Node:",d);})
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
  if (data) {
    var m = 0;
    for (var i = data.nodes.length - 1; i >= 0; i--) {
      if (data.nodes[i].depth>m)
        m = data.nodes[i].depth;
    };
    generateGraph(data,parseInt(m/2)+1);
    console.log("Max depth:"+(m+1));
    d3.select('#VN_slider').call(
      d3.slider().axis(true).min(0).max(m+1).step(1).value(parseInt(m/2)+1).on("slide", function(evt, value) {
        generateGraph(data, value);
      }));
  };
});
