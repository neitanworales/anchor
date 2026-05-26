<?php

$authController = new AuthController();
$facturaController = new FacturaController();
$facturaConceptoController = new FacturaConceptoController();
$facturaImpuestoController = new FacturaImpuestoController();
$facturaUsuarioController = new FacturaUsuarioController();
$pagoController = new PagoController();
$pagoDocumentoController = new PagoDocumentoRelacionadoController();
$cfdiRelacionadoController = new CfdiRelacionadoController();

$router->add('POST', '/api/v1/auth/login', function ($request) use ($authController) {
    return $authController->login($request);
});

$router->add('POST', '/api/v1/auth/validate', function ($request) use ($authController) {
    return $authController->validate($request);
});

$router->add('POST', '/api/v1/auth/logout', function ($request) use ($authController) {
    return $authController->logout($request);
});

$router->add('POST', '/api/v1/auth/forgot-password', function ($request) use ($authController) {
    return $authController->forgotPassword($request);
});

$router->add('POST', '/api/v1/auth/validate-reset-token', function ($request) use ($authController) {
    return $authController->validateResetToken($request);
});

$router->add('POST', '/api/v1/auth/reset-password', function ($request) use ($authController) {
    return $authController->resetPassword($request);
});

$router->add('GET', '/api/v1/facturas', function ($request) use ($facturaController) {
    return $facturaController->index($request);
});

$router->add('GET', '/api/v1/facturas/detail', function ($request) use ($facturaController) {
    return $facturaController->detail($request);
});

$router->add('GET', '/api/v1/facturas/por_usuario', function ($request) use ($facturaController) {
    return $facturaController->listByUsuario($request);
});

$router->add('GET', '/api/v1/dashboard', function ($request) use ($facturaController) {
    return $facturaController->dashboardSummary($request);
});

$router->add('GET', '/api/v1/facturas/conceptos', function ($request) use ($facturaConceptoController) {
    return $facturaConceptoController->listByFactura($request);
});

$router->add('GET', '/api/v1/facturas/impuestos', function ($request) use ($facturaImpuestoController) {
    return $facturaImpuestoController->listByFactura($request);
});

$router->add('GET', '/api/v1/facturas/pagos', function ($request) use ($pagoController) {
    return $pagoController->listByFactura($request);
});

$router->add('GET', '/api/v1/facturas/cfdi_relacionados', function ($request) use ($cfdiRelacionadoController) {
    return $cfdiRelacionadoController->listByFactura($request);
});

$router->add('POST', '/api/v1/facturas', function ($request) use ($facturaController) {
    return $facturaController->create($request);
});

$router->add('PUT', '/api/v1/facturas', function ($request) use ($facturaController) {
    return $facturaController->update($request);
});

$router->add('DELETE', '/api/v1/facturas', function ($request) use ($facturaController) {
    return $facturaController->delete($request);
});

$router->add('GET', '/api/v1/factura_usuarios', function ($request) use ($facturaUsuarioController) {
    return $facturaUsuarioController->index($request);
});

$router->add('GET', '/api/v1/factura_usuarios/detail', function ($request) use ($facturaUsuarioController) {
    return $facturaUsuarioController->detail($request);
});

$router->add('POST', '/api/v1/factura_usuarios', function ($request) use ($facturaUsuarioController) {
    return $facturaUsuarioController->create($request);
});

$router->add('PUT', '/api/v1/factura_usuarios', function ($request) use ($facturaUsuarioController) {
    return $facturaUsuarioController->update($request);
});

$router->add('DELETE', '/api/v1/factura_usuarios', function ($request) use ($facturaUsuarioController) {
    return $facturaUsuarioController->delete($request);
});

$router->add('GET', '/api/v1/factura_conceptos', function ($request) use ($facturaConceptoController) {
    return $facturaConceptoController->index($request);
});

$router->add('GET', '/api/v1/factura_conceptos/detail', function ($request) use ($facturaConceptoController) {
    return $facturaConceptoController->detail($request);
});

$router->add('POST', '/api/v1/factura_conceptos', function ($request) use ($facturaConceptoController) {
    return $facturaConceptoController->create($request);
});

$router->add('PUT', '/api/v1/factura_conceptos', function ($request) use ($facturaConceptoController) {
    return $facturaConceptoController->update($request);
});

$router->add('DELETE', '/api/v1/factura_conceptos', function ($request) use ($facturaConceptoController) {
    return $facturaConceptoController->delete($request);
});

$router->add('GET', '/api/v1/factura_impuestos', function ($request) use ($facturaImpuestoController) {
    return $facturaImpuestoController->index($request);
});

$router->add('GET', '/api/v1/factura_impuestos/detail', function ($request) use ($facturaImpuestoController) {
    return $facturaImpuestoController->detail($request);
});

$router->add('POST', '/api/v1/factura_impuestos', function ($request) use ($facturaImpuestoController) {
    return $facturaImpuestoController->create($request);
});

$router->add('PUT', '/api/v1/factura_impuestos', function ($request) use ($facturaImpuestoController) {
    return $facturaImpuestoController->update($request);
});

$router->add('DELETE', '/api/v1/factura_impuestos', function ($request) use ($facturaImpuestoController) {
    return $facturaImpuestoController->delete($request);
});

$router->add('GET', '/api/v1/pagos', function ($request) use ($pagoController) {
    return $pagoController->index($request);
});

$router->add('GET', '/api/v1/pagos/detail', function ($request) use ($pagoController) {
    return $pagoController->detail($request);
});

$router->add('GET', '/api/v1/pagos/documentos', function ($request) use ($pagoDocumentoController) {
    return $pagoDocumentoController->listByPago($request);
});

$router->add('POST', '/api/v1/pagos', function ($request) use ($pagoController) {
    return $pagoController->create($request);
});

$router->add('PUT', '/api/v1/pagos', function ($request) use ($pagoController) {
    return $pagoController->update($request);
});

$router->add('DELETE', '/api/v1/pagos', function ($request) use ($pagoController) {
    return $pagoController->delete($request);
});

$router->add('GET', '/api/v1/pagos_documentos_relacionados', function ($request) use ($pagoDocumentoController) {
    return $pagoDocumentoController->index($request);
});

$router->add('GET', '/api/v1/pagos_documentos_relacionados/detail', function ($request) use ($pagoDocumentoController) {
    return $pagoDocumentoController->detail($request);
});

$router->add('POST', '/api/v1/pagos_documentos_relacionados', function ($request) use ($pagoDocumentoController) {
    return $pagoDocumentoController->create($request);
});

$router->add('PUT', '/api/v1/pagos_documentos_relacionados', function ($request) use ($pagoDocumentoController) {
    return $pagoDocumentoController->update($request);
});

$router->add('DELETE', '/api/v1/pagos_documentos_relacionados', function ($request) use ($pagoDocumentoController) {
    return $pagoDocumentoController->delete($request);
});

$router->add('GET', '/api/v1/cfdi_relacionados', function ($request) use ($cfdiRelacionadoController) {
    return $cfdiRelacionadoController->index($request);
});

$router->add('GET', '/api/v1/cfdi_relacionados/detail', function ($request) use ($cfdiRelacionadoController) {
    return $cfdiRelacionadoController->detail($request);
});

$router->add('POST', '/api/v1/cfdi_relacionados', function ($request) use ($cfdiRelacionadoController) {
    return $cfdiRelacionadoController->create($request);
});

$router->add('PUT', '/api/v1/cfdi_relacionados', function ($request) use ($cfdiRelacionadoController) {
    return $cfdiRelacionadoController->update($request);
});

$router->add('DELETE', '/api/v1/cfdi_relacionados', function ($request) use ($cfdiRelacionadoController) {
    return $cfdiRelacionadoController->delete($request);
});