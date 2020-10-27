<!DOCTYPE html>
<html>
	<head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Morphic!</title>
		<script type="text/javascript" src="/js/morphic.js"></script>
        <script type="text/javascript" src="/js/canvas.js"></script>
        <script type="text/javascript">
        
            var rootConcept = {!! json_encode($concept) !!};

			var	worldCanvas, world, hi, hint1, hint2;

			window.onload = function () {
				worldCanvas = document.getElementById('world');
				world = new WorldMorph(worldCanvas);
                // +++ world.worldCanvas.focus();
				world.isDevMode = true;

				rootMorph = new ConceptMorph(rootConcept);
				rootMorph.setPosition(new Point(275, 200));
				world.add(rootMorph);

                loop();
			};

			function loop() {
                requestAnimationFrame(loop);
                world.doOneCycle();
			};
		</script>
	</head>

	<body style="margin: 0;">
		<canvas id="world" tabindex="1" width="800" height="600" style="position: absolute; left: 0px; right: 0px; width: 100%; height: 100%;" />
	</body>
</html>
