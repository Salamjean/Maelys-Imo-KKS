use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

public function register()
{
    $this->renderable(function (NotFoundHttpException $e, $request) {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Resource not found'], 404);
        }
        return response()->view('errors.404', [], 404);
    });

    $this->renderable(function (\Exception $e, $request) {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Server error'], 500);
        }
        return response()->view('errors.500', [], 500);
    });
}