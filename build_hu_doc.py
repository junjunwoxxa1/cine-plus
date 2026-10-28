import docx
from docx.shared import Inches, Pt, RGBColor
from docx.oxml import OxmlElement, parse_xml
from docx.oxml.ns import nsdecls, qn
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT

# Paleta de colores acorde a las imágenes adjuntadas por el usuario
COLOR_HEADER_BG = "5D1421"      # Granate / Borgoña oscuro institucional
COLOR_HEADER_TXT = "FFFFFF"     # Blanco
COLOR_CODE_TXT = "8B1E2D"       # Rojo oscuro para Códigos y Módulos
COLOR_BORDER = "CCCCCC"         # Gris claro sutil para celdas

def set_cell_margins(cell, top=100, bottom=100, left=150, right=150):
    tc = cell._tc
    tcPr = tc.get_or_add_tcPr()
    tcMar = OxmlElement('w:tcMar')
    for m, val in [('top', top), ('bottom', bottom), ('left', left), ('right', right)]:
        node = OxmlElement(f'w:{m}')
        node.set(qn('w:w'), str(val))
        node.set(qn('w:type'), 'dxa')
        tcMar.append(node)
    tcPr.append(tcMar)

def set_cell_background(cell, hex_color):
    shading_xml = f'<w:shd {nsdecls("w")} w:fill="{hex_color}"/>'
    cell._tc.get_or_add_tcPr().append(parse_xml(shading_xml))

def set_cell_borders(cell, top="CCCCCC", bottom="CCCCCC", left="CCCCCC", right="CCCCCC"):
    borders_xml = f'''
    <w:tcBorders {nsdecls("w")}>
        <w:top w:val="single" w:sz="4" w:space="0" w:color="{top}"/>
        <w:bottom w:val="single" w:sz="4" w:space="0" w:color="{bottom}"/>
        <w:left w:val="single" w:sz="4" w:space="0" w:color="{left}"/>
        <w:right w:val="single" w:sz="4" w:space="0" w:color="{right}"/>
    </w:tcBorders>
    '''
    cell._tc.get_or_add_tcPr().append(parse_xml(borders_xml))

def apply_table_styles(table, col_widths):
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    table.autofit = False
    
    # Header styling
    for i, cell in enumerate(table.rows[0].cells):
        set_cell_background(cell, COLOR_HEADER_BG)
        set_cell_margins(cell, top=140, bottom=140, left=160, right=160)
        set_cell_borders(cell, top="4A0F1A", bottom="4A0F1A", left="4A0F1A", right="4A0F1A")
        cell.width = col_widths[i]
        for p in cell.paragraphs:
            p.alignment = WD_ALIGN_PARAGRAPH.LEFT
            for run in p.runs:
                run.font.bold = True
                run.font.name = "Arial"
                run.font.size = Pt(9.5)
                run.font.color.rgb = RGBColor(255, 255, 255)
                
    # Data rows styling
    for r_idx, row in enumerate(table.rows[1:], start=1):
        bg = "FBFBFB" if r_idx % 2 == 0 else "FFFFFF"
        for i, cell in enumerate(row.cells):
            set_cell_background(cell, bg)
            set_cell_margins(cell, top=100, bottom=100, left=140, right=140)
            set_cell_borders(cell, top=COLOR_BORDER, bottom=COLOR_BORDER, left=COLOR_BORDER, right=COLOR_BORDER)
            cell.width = col_widths[i]
            for p in cell.paragraphs:
                for run in p.runs:
                    run.font.name = "Arial"
                    run.font.size = Pt(8.5)
                    if i == 0 or i == 1:
                        run.font.bold = True
                        run.font.color.rgb = RGBColor(139, 30, 45) # Rojo borgoña como la plantilla
                    else:
                        run.font.color.rgb = RGBColor(40, 40, 40)

# Datos de Historias de Usuario Consolidadas (HU-01 a HU-63)
HUs = [
    # Documento original (HU-01 a HU-45)
    ("HU-01", "Módulo de consulta y cartelera", "Usuario", "Como Usuario, quiero buscar una película, para encontrar la película que deseo ver.", "RN01"),
    ("HU-02", "Módulo de consulta y cartelera", "Usuario", "Como Usuario, quiero consultar los horarios y salas disponibles de una película, para elegir la función que prefiero.", "RN01, RN24, RN28"),
    ("HU-03", "Módulo de consulta y cartelera", "Usuario", "Como Usuario, quiero seleccionar una función, para continuar con la compra de entradas.", "RN01"),
    ("HU-04", "Módulo de compra de entradas", "Usuario", "Como Usuario, quiero indicar la cantidad de entradas que deseo comprar, para determinar cuántas butacas necesito.", "RN01, RN02"),
    ("HU-05", "Módulo de compra de entradas", "Usuario", "Como Usuario, quiero seleccionar las butacas de mi compra, para elegir las ubicaciones que prefiero.", "RN02, RN28"),
    ("HU-06", "Módulo de compra de entradas", "Usuario", "Como Usuario, quiero conservar provisionalmente las butacas seleccionadas, para evitar que otro usuario las seleccione mientras completo la compra.", "RN02, RN03, RN05, RN28"),
    ("HU-07", "Módulo de compra de entradas", "Usuario", "Como Usuario, quiero cancelar la compra en proceso, para liberar las butacas que había reservado.", "RN04"),
    ("HU-08", "Módulo de compra de entradas", "Usuario", "Como Usuario, quiero indicar que tengo derecho a un descuento, para obtener el precio correspondiente de la entrada.", "RN06, RN07, RN08, RN09"),
    ("HU-09", "Módulo de compra de entradas", "Usuario", "Como Usuario, quiero pagar mi compra por internet, para completar la adquisición de las entradas.", "RN10"),
    ("HU-10", "Módulo de compra de entradas", "Usuario", "Como Usuario, quiero imprimir la entrada que compré por internet, para disponer de ella en formato físico.", "RN11"),
    ("HU-11", "Módulo de socios Cine +", "Usuario", "Como Usuario, quiero registrarme como socio del club Cine +, para participar en el programa de puntos.", "RN12"),
    ("HU-12", "Módulo de compra de entradas", "Usuario", "Como Usuario, quiero anular una compra realizada por internet, para utilizar el importe de la compra en otra función.", "RN18, RN19, RN20, RN21"),
    ("HU-13", "Módulo de consulta y cartelera", "Usuario", "Como Usuario, quiero visualizar las diez películas más vistas de la última semana, para conocer las películas con mayor popularidad.", "RN26, RN27"),
    ("HU-14", "Módulo de socios Cine +", "Socio Cine +", "Como Socio Cine +, quiero acumular puntos por mis compras de entradas, para utilizarlos posteriormente.", "RN13, RN14"),
    ("HU-15", "Módulo de socios Cine +", "Socio Cine +", "Como Socio Cine +, quiero utilizar mis puntos para obtener entradas, para adquirirlas sin utilizar dinero.", "RN15, RN16"),
    ("HU-16", "Módulo de socios Cine +", "Socio Cine +", "Como Socio Cine +, quiero canjear mis puntos por alimentos o bebidas, para obtener productos del cine utilizando mis puntos.", "RN17"),
    ("HU-17", "Módulo de socios Cine +", "Empleado de Ventanilla", "Como Empleado de Ventanilla, quiero registrar los datos del formulario de inscripción de un usuario, para darlo de alta como socio del club Cine +.", "RN12"),
    ("HU-18", "Módulo de venta en ventanilla", "Empleado de Ventanilla", "Como Empleado de Ventanilla, quiero registrar una venta de entradas, para atender la compra presencial del usuario.", "RN01, RN02, RN13, RN28"),
    ("HU-19", "Módulo de venta en ventanilla", "Empleado de Ventanilla", "Como Empleado de Ventanilla, quiero registrar el pago de una compra, para completar la venta de entradas.", "RN29"),
    ("HU-20", "Módulo de gestión del cine", "Gerente", "Como Gerente, quiero actualizar el listado de películas, para mantener vigente la cartelera del cine.", "RN22, RN24"),
    ("HU-21", "Módulo de gestión del cine", "Gerente", "Como Gerente, quiero actualizar los horarios y salas disponibles, para mantener vigente la programación del cine.", "RN23, RN24"),
    ("HU-22", "Módulo de gestión del cine", "Gerente", "Como Gerente, quiero consultar las estadísticas de venta de entradas, para analizar el comportamiento de las ventas del cine.", "RN25"),
    ("HU-23", "Módulo de alimentos y bebidas", "Usuario", "Como Usuario, quiero consultar los alimentos, bebidas, canchita y combos disponibles, para elegir los productos que deseo comprar por la web.", "RN30"),
    ("HU-24", "Módulo de alimentos y bebidas", "Usuario", "Como Usuario, quiero seleccionar alimentos, bebidas, canchita y combos, para realizar mi compra por la web.", "RN30, RN31, RN32"),
    ("HU-25", "Módulo de alimentos y bebidas", "Usuario", "Como Usuario, quiero pagar mi compra de alimentos, bebidas, canchita y combos por internet, para completar la adquisición.", "RN10, RN33"),
    ("HU-26", "Módulo de alimentos y bebidas", "Empleado de Ventanilla", "Como Empleado de Ventanilla, quiero consultar los alimentos, bebidas, canchita y combos disponibles, para registrar la compra solicitada.", "RN30"),
    ("HU-27", "Módulo de alimentos y bebidas", "Empleado de Ventanilla", "Como Empleado de Ventanilla, quiero seleccionar alimentos, bebidas, canchita y combos, para registrar los productos solicitados en la compra.", "RN30, RN31, RN32"),
    ("HU-28", "Módulo de alimentos y bebidas", "Empleado de Ventanilla", "Como Empleado de Ventanilla, quiero registrar una compra de alimentos, bebidas, canchita y combos, para dejar constancia de la venta realizada.", "RN31, RN32"),
    ("HU-29", "Módulo de alimentos y bebidas", "Empleado de Ventanilla", "Como Empleado de Ventanilla, quiero registrar el pago de una compra de alimentos, bebidas, canchita y combos, para completar la venta.", "RN29, RN33"),
    ("HU-30", "Módulo de compra de entradas", "Usuario", "Como Usuario, quiero retroceder desde la dulcería a la selección de butacas sin que mis asientos se queden bloqueados, para poder cambiar de butacas o reelegir mis asientos sin errores.", "RN02, RN03, RN04, RN34"),
    ("HU-31", "Módulo de compra de entradas", "Usuario", "Como Usuario, quiero que mis productos de dulcería se mantengan en el carrito si regreso a modificar mis asientos, para no tener que volver a elegir mis snacks desde cero.", "RN30, RN35"),
    ("HU-32", "Módulo de alimentos y bebidas", "Usuario", "Como Usuario, quiero visualizar en color gris y deshabilitados los productos desactivados o sin stock, para saber claramente qué snacks no están disponibles y no poder comprarlos.", "RN30, RN36"),
    ("HU-33", "Módulo de gestión del cine", "Gerente / Administrador", "Como Gerente, quiero activar, desactivar o modificar el stock de los productos de dulcería desde el panel de control, para regular la disponibilidad de snacks en tiempo real en la web y ventanilla.", "RN36, RN37"),
    ("HU-34", "Módulo de comprobantes y boletas", "Usuario", "Como Usuario, quiero descargar directamente mi comprobante oficial en formato PDF con código QR y desglose de IGV (18%), para tener mi boleta digital guardada en mi dispositivo y presentarla en sala.", "RN10, RN11, RN38"),
    ("HU-35", "Módulo de comprobantes y boletas", "Usuario", "Como Usuario, quiero recibir automáticamente una copia de mi comprobante con el PDF adjunto en mi correo electrónico, para disponer de mi respaldo de compra en mi bandeja de entrada.", "RN38, RN39"),
    ("HU-36", "Módulo de comprobantes y boletas", "Usuario", "Como Usuario, quiero consultar mis compras históricas o buscar una orden por código de reserva o correo, para volver a ver mi ticket, el código QR y descargar el PDF en cualquier momento.", "RN38, RN40"),
    ("HU-37", "Módulo de alimentos y bebidas", "Usuario", "Como Usuario, quiero comprar productos y combos de dulcería de forma online independiente sin necesidad de comprar una entrada, para recoger mis snacks rápido en el módulo express del cine.", "RN30, RN33, RN41"),
    ("HU-38", "Módulo de punto de venta", "Cajero de Dulcería", "Como Cajero de Dulcería, quiero registrar ventas presenciales en un terminal táctil interactivo con carrito reactivo y cobro inmediato, para atender clientes en mostrador y descontar el stock en tiempo real.", "RN29, RN31, RN42"),
    ("HU-39", "Módulo de gestión del cine", "Gerente / Administrador", "Como Gerente, quiero crear y administrar cupones de descuento con código promocional y vigencia, para ofrecer promociones dinámicas a los clientes.", "RN06, RN08, RN43"),
    ("HU-40", "Módulo de compra de entradas", "Usuario", "Como Usuario, quiero ingresar un código promocional en la pantalla de pago y validar su descuento de inmediato, para pagar un precio reducido por mis entradas.", "RN08, RN43, RN44"),
    ("HU-41", "Módulo de seguridad y autenticación", "Usuario", "Como Usuario, quiero iniciar sesión o recuperar mi cuenta mediante un código de acceso temporal enviado a mi correo, para ingresar de forma segura y rápida sin depender de recordar contraseñas.", "RN45"),
    ("HU-42", "Módulo de gestión del cine", "Gerente / Administrador", "Como Gerente, quiero configurar y habilitar métodos de pago definiendo si requieren formulario de tarjeta, para brindar opciones de cobro flexibles y seguras.", "RN10, RN46"),
    ("HU-43", "Módulo de gestión del cine", "Gerente / Administrador", "Como Gerente, quiero configurar la distribución arquitectónica de cada sala, para que el mapa interactivo refleje la sala real.", "RN23, RN28, RN47"),
    ("HU-44", "Módulo de consulta y cartelera", "Usuario", "Como Usuario, quiero ver en la portada un banner dinámico interactivo con las películas destacadas y sus funciones más próximas, para consultar y elegir directamente las funciones de hoy.", "RN01, RN48"),
    ("HU-45", "Módulo de gestión del cine", "Gerente / Administrador", "Como Gerente, quiero gestionar los usuarios del sistema y asignar roles de acceso, para restringir el acceso al panel administrativo y al terminal de venta.", "RN49"),
    
    # Nuevas HUs incorporadas por mejoras y requerimientos del sistema
    ("HU-46", "Módulo de comprobantes y boletas", "Usuario", "Como Usuario, quiero imprimir mi comprobante y ticket de entrada optimizado en una sola página física sin avisos decorativos duplicados, para disponer de mi boleto en formato limpio y compacto.", "RN11, RN50"),
    ("HU-47", "Módulo de gestión del cine", "Gerente / Administrador", "Como Gerente, quiero que el sistema aplique validación antiduplicidad al crear salas y horarios, para evitar programaciones dobles o colisión de horarios en una misma sala.", "RN23, RN51"),
    ("HU-48", "Módulo de socios Cine +", "Gerente / Administrador", "Como Gerente, quiero configurar a nivel global la división de puntos en categorías independientes (Puntos Cine vs. Puntos Dulcería), para definir la política de acumulación y canje diferenciado en el sistema.", "RN13, RN52"),
    ("HU-49", "Módulo de socios Cine +", "Socio Cine +", "Como Socio Cine +, quiero visualizar de manera diferenciada mis puntos acumulados de Cine y de Dulcería tanto en mi perfil como en la barra superior, para conocer mi saldo disponible en cada rubro.", "RN14, RN52, RN53"),
    ("HU-50", "Módulo de socios Cine +", "Socio Cine +", "Como Socio Cine +, quiero canjear mis puntos de dulcería exclusivamente por alimentos y mis puntos de cine por entradas según la política activa, para aprovechar mis beneficios según cada categoría de consumo.", "RN15, RN17, RN53"),
    ("HU-51", "Módulo de gestión de sedes", "Gerente / Administrador", "Como Gerente, quiero registrar, listar y administrar las diferentes sedes del cine y vincularlas a las funciones, para gestionar la oferta cinematográfica de múltiples complejos de cine.", "RN23, RN54"),
    ("HU-52", "Módulo de consulta y cartelera", "Usuario", "Como Usuario, quiero filtrar la cartelera y las funciones por la sede de mi preferencia, para encontrar proyecciones en el complejo cinematográfico más conveniente.", "RN01, RN54"),
    ("HU-53", "Módulo de punto de venta", "Gerente / Administrador", "Como Gerente, quiero acceder al terminal de Punto de Venta (POS) de dulcería y taquilla con permisos administrativos, para registrar ventas directas de contingencia y supervisar la caja en ventanilla.", "RN29, RN42, RN55"),
    ("HU-54", "Módulo de cancelaciones y devoluciones", "Usuario", "Como Usuario, quiero cancelar de manera automática mi compra de entradas hasta con 12 horas de anticipación a la función, para anular mi boleto y liberar las butacas en tiempo real sin trámites presenciales.", "RN18, RN56, RN57"),
    ("HU-55", "Módulo de cancelaciones y devoluciones", "Sistema", "Como Sistema, quiero rechazar automáticamente cualquier solicitud de cancelación cuando resten menos de 12 horas para el inicio de la función o sea una función de estreno inmediato, para proteger la ocupación y venta de la sala.", "RN56"),
    
    # NUEVAS HUs DERIVADAS DE LOS ÚLTIMOS ARREGLOS ESPECÍFICOS
    ("HU-56", "Módulo de socios Cine +", "Socio Cine +", "Como Socio Cine +, quiero canjear mis 50 puntos acumulados para obtener el descuento del 100% en exactamente una (1) entrada de mi compra, de modo que al adquirir dos o más entradas solo se descuente el valor de una entrada por cada lote de 50 puntos canjeados.", "RN15, RN16, RN58"),
    ("HU-57", "Módulo de cancelaciones y comprobantes", "Usuario", "Como Usuario, quiero ver mis entradas activas y cancelar mi compra mediante un botón directo en el mismo ticket con confirmación instantánea, para anular mi reserva de forma ágil sin tener que recordar ni ingresar manualmente el código alfanumérico de reserva.", "RN18, RN56, RN57, RN59"),
    ("HU-58", "Módulo de comprobantes y boletas", "Usuario", "Como Usuario, quiero que mis entradas compradas se mantengan en la sección de 'Vigentes / Próximas Funciones' hasta que culmine el día y horario programado de la función, pasando de forma automática al historial pasado al concluir la proyección.", "RN38, RN40, RN60"),
    ("HU-59", "Módulo de consulta y cartelera", "Usuario", "Como Usuario, quiero seleccionar la sede y el día de la película para ver instantáneamente en tiempo real solo los horarios disponibles y vigentes, simplificando la selección sin filtros innecesarios de turnos.", "RN01, RN24, RN54, RN61"),
    ("HU-60", "Módulo de gestión del cine", "Gerente / Administrador", "Como Gerente, quiero visualizar en color rojo y con etiqueta de 'Ocupado' los horarios ya programados para una sala y día determinados al crear una nueva función, para evitar colisiones y organizar la grilla de proyecciones con claridad visual inmediata.", "RN23, RN51, RN62"),
    ("HU-61", "Módulo de cancelaciones y devoluciones", "Sistema", "Como Sistema, quiero calcular la diferencia de horas para la regla de cancelación automática utilizando la zona horaria local oficial (America/Lima, UTC-5), garantizando que las funciones con más de 12 horas reales de anticipación permitan la cancelación sin desfases de huso horario.", "RN56, RN63"),
    ("HU-62", "Módulo de punto de venta", "Gerente / Administrador", "Como Gerente, quiero tener botones y enlaces de acceso directo al POS de Dulcería desde el panel principal de administración, barra superior y menú desplegable, para entrar inmediatamente al terminal de venta física sin impedimentos de permisos ni errores de navegación.", "RN29, RN42, RN55, RN64"),
    ("HU-63", "Módulo de comprobantes y boletas", "Usuario", "Como Usuario, quiero visualizar el código QR de mi entrada perfectamente encajado dentro de su contenedor blanco en la tarjeta del ticket y en el comprobante digital, para asegurar su correcta legibilidad y escaneo rápido en la entrada a la sala sin desbordes.", "RN11, RN38, RN65")
]

# Matriz de Reglas de Negocio (RN01 a RN65)
RNs = [
    ("RN01", "La consulta de películas en cartelera y horarios disponibles es de acceso público y libre."),
    ("RN02", "La selección de butacas requiere indicar previamente la función y cantidad de entradas."),
    ("RN03", "Las butacas seleccionadas se reservan provisionalmente por un tiempo límite de 10 minutos."),
    ("RN04", "Si se cancela la compra o expira el tiempo límite, las butacas se liberan automáticamente."),
    ("RN05", "Ningún otro usuario puede seleccionar butacas reservadas provisionalmente."),
    ("RN06", "Los descuentos se aplican únicamente a usuarios que cumplan con las condiciones establecidas."),
    ("RN07", "Los descuentos aplicables pueden ser por promociones bancarias, convenios o días especiales."),
    ("RN08", "Solo se puede aplicar un tipo de descuento por compra."),
    ("RN09", "El sistema debe recalcular el monto total luego de aplicar el descuento."),
    ("RN10", "El pago por internet requiere validación de los datos de la tarjeta a través de una pasarela segura."),
    ("RN11", "La entrada impresa o digital debe contener los datos de la función, sala, butacas y un código QR único."),
    ("RN12", "El registro en el club Cine + requiere datos personales válidos (nombre, DNI, correo electrónico)."),
    ("RN13", "Por cada compra de entradas, el socio acumula puntos en su cuenta (10 puntos por compra)."),
    ("RN14", "Los puntos acumulados tienen una vigencia de un año desde la fecha de emisión."),
    ("RN15", "El canje de entradas por puntos se realiza según la tabla de equivalencias vigente (50 puntos por entrada)."),
    ("RN16", "Las entradas canjeadas con puntos no generan acumulación de nuevos puntos."),
    ("RN17", "Los puntos pueden canjearse por combos seleccionados de dulcería según la disponibilidad."),
    ("RN18", "La anulación de compras está sujeta a políticas de plazo mínimo antes de la función."),
    ("RN19", "En caso de anulación aprobada, el importe se reembolsa o se genera una nota de crédito."),
    ("RN20", "Las compras anuladas liberan inmediatamente las butacas para nueva venta."),
    ("RN21", "No se permiten anulaciones de funciones que ya hayan iniciado."),
    ("RN22", "El listado de películas debe contener título, sinopsis, género, clasificación y duración."),
    ("RN23", "La programación de funciones debe respetar la disponibilidad de salas y no solaparse."),
    ("RN24", "Las películas sin funciones programadas no se mostrarán en la cartelera activa."),
    ("RN25", "Los reportes de ventas solo son accesibles por usuarios con rol de Gerente o Administrador."),
    ("RN26", "El ranking de las 10 películas más vistas se calcula automáticamente en base a las ventas de los últimos 7 días."),
    ("RN27", "El ranking se actualiza diariamente al cierre de operaciones."),
    ("RN28", "La capacidad máxima de la sala no puede ser superada bajo ninguna circunstancia."),
    ("RN29", "Las ventas en ventanilla admiten pago en efectivo, tarjeta de crédito/débito o billeteras digitales."),
    ("RN30", "El catálogo de dulcería muestra únicamente productos y combos con stock disponible mayor a cero."),
    ("RN31", "La compra de snacks puede realizarse en conjunto con las entradas o como pedido express independiente."),
    ("RN32", "Cada ítem de dulcería seleccionado descuenta temporalmente una reserva de inventario."),
    ("RN33", "El pago de dulcería emite boleta electrónica con detalle de productos e IGV desglosado."),
    ("RN34", "Al regresar del paso de dulcería al mapa de asientos, el sistema preserva la sesión activa y renueva el bloqueo temporal sin errores de colisión."),
    ("RN35", "Los productos de confitería seleccionados en el carrito se conservan intactos en la sesión mientras no se cancele voluntariamente el flujo."),
    ("RN36", "Los productos con stock en cero o marcados como inactivos se renderizan deshabilitados en escala de grises."),
    ("RN37", "Los ajustes de inventario de snacks realizados por administración impactan de forma sincrónica en la web y terminales POS."),
    ("RN38", "Todo comprobante emitido posee una serie alfanumérica correlativa y código hash QR de verificación ante SUNAT."),
    ("RN39", "El envío de correos transaccionales adjunta el comprobante tributario en formato PDF nativo."),
    ("RN40", "La consulta pública de reservas valida de forma estricta la coincidencia del código de reserva y/o el correo del titular."),
    ("RN41", "Las órdenes de dulcería independientes generan un código de recojo para el mostrador express del cine."),
    ("RN42", "El terminal POS opera en modo pantalla táctil optimizado para cajeros con arqueo de caja y cobro ágil."),
    ("RN43", "Los cupones de descuento validan fecha de vigencia, tope máximo de usos y monto mínimo de compra."),
    ("RN44", "El sistema rechaza códigos promocionales expirados o incompatibles con otras promociones activas."),
    ("RN45", "La autenticación por enlace seguro OTP remite un código numérico temporal de 6 dígitos con expiración de 10 minutos."),
    ("RN46", "Los métodos de pago administrables permiten activar o desactivar pasarelas y cobros manuales en tiempo real."),
    ("RN47", "La matriz de filas y columnas de una sala se almacena con coordenadas para renderizado visual idéntico."),
    ("RN48", "El banner de portada muestra las películas marcadas como 'destacadas' con acceso directo a sus horarios de hoy."),
    ("RN49", "El sistema implementa control de acceso basado en roles (RBAC) con separación de privilegios."),
    ("RN50", "La vista de impresión del comprobante (@media print) suprime avisos decorativos para ajustarse estrictamente a una única página física."),
    ("RN51", "El módulo de creación de salas y horarios valida la unicidad de nombres y solapamientos horarios para impedir registros duplicados."),
    ("RN52", "La acumulación y canje de puntos se rige por un parámetro global configurable que define si los puntos son compartidos o divididos."),
    ("RN53", "Bajo el modo de puntos divididos, los puntos obtenidos por compras de cine se acumulan y canjean en 'Puntos Cine', y los de dulcería en 'Puntos Dulcería'."),
    ("RN54", "Cada sede del cine posee nombre, dirección y salas asociadas; las funciones se programan por sede para filtrado directo en cartelera."),
    ("RN55", "Los usuarios con rol Administrador tienen habilitado el acceso completo al módulo POS para ventas y contingencias de mostrador."),
    ("RN56", "La cancelación automática por el cliente exige un plazo mínimo de 12 horas antes del inicio de la función. Funciones próximas o de estreno no admiten cancelación."),
    ("RN57", "La cancelación automática revierte el estado a 'anulado', libera las butacas en la matriz de la función, repone inventario y ajusta los puntos otorgados."),
    ("RN58", "El canje de 50 puntos de cine aplica un descuento equivalente al valor unitario de exactamente una (1) entrada; cuando una orden contiene múltiples entradas, el descuento se limita a una sola entrada por canje, requiriendo el pago íntegro de las entradas restantes."),
    ("RN59", "La cancelación de una compra puede ejecutarse directamente desde el contenedor interactivo del ticket en 'Mis Compras' mediante un botón de confirmación, validando los permisos del usuario o sesión sin requerir la digitación manual del código de reserva."),
    ("RN60", "La vigencia de un ticket se determina dinámicamente comparando la fecha/hora de la función con la hora actual; el comprobante permanece en 'Entradas Activas' hasta 3 horas posteriores al inicio de la función, tras lo cual se reclasifica automáticamente en 'Historial Pasado'."),
    ("RN61", "El catálogo de horarios por película se filtra en tiempo real mediante Livewire interactivo únicamente por 'Sede' y 'Día', omitiendo selectores redundantes de turno y mostrando solo las proyecciones activas para los criterios elegidos."),
    ("RN62", "En el formulario de creación de funciones, al seleccionar sala y fecha, el sistema consulta y resalta en rojo los horarios ya reservados para esa combinación, advirtiendo al administrador sobre franjas horarias ocupadas para prevenir colisiones."),
    ("RN63", "Todas las validaciones temporales del sistema, en especial la ventana de 12 horas para cancelación automática, se ejecutan en sincronía con la zona horaria oficial America/Lima (UTC-5), descartando desfases por zonas horarias del servidor o navegador."),
    ("RN64", "El usuario con rol Administrador (role_id = 1) posee autorización intrínseca en el Gate 'access-pos' y cuenta con accesos directos visibles en el Dashboard administrativo, barra superior y menú de usuario."),
    ("RN65", "El código QR digital en comprobantes y tickets web se renderiza en formato SVG vectorial adaptativo con encaje simétrico en su contenedor blanco para lectura óptica inmediata en puertas de sala.")
]

# Construcción del Documento Word
doc = docx.Document()

# Configuración de márgenes de página
for section in doc.sections:
    section.top_margin = Inches(0.8)
    section.bottom_margin = Inches(0.8)
    section.left_margin = Inches(0.8)
    section.right_margin = Inches(0.8)

# Título Principal
h1 = doc.add_paragraph()
r = h1.add_run("4. Tabla de historias de usuario")
r.font.name = "Arial"
r.font.size = Pt(16)
r.font.bold = True
r.font.color.rgb = RGBColor(93, 20, 33)

p_intro = doc.add_paragraph()
r_intro = p_intro.add_run(
    "A continuación se presenta la relación completa, ampliada y actualizada de las Historias de Usuario "
    "(HU-01 a HU-63) del sistema Cine+, integrando las funcionalidades consolidadas de cartelera, venta de entradas, "
    "confitería, programa de puntos individuales (Cine vs. Dulcería), gestión de múltiples sedes, terminal de punto de venta (POS), "
    "y la política de cancelación automática con ventana de 12 horas y sincronización horaria local."
)
r_intro.font.name = "Arial"
r_intro.font.size = Pt(9.5)

# 1. Tabla General Consolidada
col_widths_general = [Inches(0.9), Inches(1.8), Inches(1.3), Inches(4.0), Inches(1.4)]
table_gen = doc.add_table(rows=1, cols=5)
headers_gen = ["Código", "Módulo", "Actor", "Historia de usuario", "Reglas de negocio"]
for i, h in enumerate(headers_gen):
    table_gen.rows[0].cells[i].paragraphs[0].text = h

for hu in HUs:
    row = table_gen.add_row()
    for i in range(5):
        row.cells[i].paragraphs[0].text = hu[i]

apply_table_styles(table_gen, col_widths_general)

doc.add_paragraph() # Espaciador

# 2. SECCIÓN DE FLUJOS POR ROL DE USUARIO
h2 = doc.add_paragraph()
r = h2.add_run("5. Historias de Usuario por Rol de Usuario y Flujo del Sistema")
r.font.name = "Arial"
r.font.size = Pt(15)
r.font.bold = True
r.font.color.rgb = RGBColor(93, 20, 33)

p_flujos = doc.add_paragraph()
r_flujos = p_flujos.add_run(
    "En esta sección se reorganizan las Historias de Usuario agrupadas por cada tipo de actor y ordenadas cronológicamente "
    "siguiendo el flujo secuencial y natural de ejecución en el sistema: desde el acceso y exploración de cartelera hasta la selección "
    "de butacas, dulcería, pago, emisión de comprobantes con QR adaptativo, gestión de post-venta y cancelación automática directa."
)
r_flujos.font.name = "Arial"
r_flujos.font.size = Pt(9.5)

# --- 5.1 Flujo Usuario / Cliente ---
h3_user = doc.add_paragraph()
r = h3_user.add_run("5.1 Flujo de Usuario / Cliente (Experiencia Digital de Compra, Consulta y Cancelación)")
r.font.name = "Arial"
r.font.size = Pt(12)
r.font.bold = True
r.font.color.rgb = RGBColor(139, 30, 45)

p_user_desc = doc.add_paragraph()
r = p_user_desc.add_run(
    "El cliente interactúa secuencialmente a través de 8 fases operativas: "
    "(1) Ingreso y autenticación por OTP o registro en el club; "
    "(2) Exploración en cartelera, banner interactivo y filtrado en tiempo real únicamente por Sede y Día; "
    "(3) Selección de función y visualización de horarios disponibles; "
    "(4) Selección de butacas con bloqueo temporal y retorno seguro entre pasos; "
    "(5) Dulcería y snacks con disponibilidad sincronizada o compra express independiente; "
    "(6) Aplicación de cupones o promociones y pasarela de pago; "
    "(7) Emisión, descarga en PDF e impresión en 1 página de boletas con código QR fluido; y "
    "(8) Consulta de entradas en 'Vigentes' o 'Historial Pasado' y cancelación automática directa desde el ticket con validación estricta de 12 horas en hora peruana."
)
r.font.name = "Arial"
r.font.size = Pt(9)

codigos_user = [
    # Fase 1: Acceso
    "HU-41", "HU-11",
    # Fase 2: Consulta y Cartelera
    "HU-44", "HU-01", "HU-13", "HU-52", "HU-59",
    # Fase 3: Selección de Función
    "HU-02", "HU-03",
    # Fase 4: Butacas
    "HU-04", "HU-05", "HU-06", "HU-07", "HU-30",
    # Fase 5: Dulcería
    "HU-23", "HU-24", "HU-31", "HU-32", "HU-37",
    # Fase 6: Descuentos y Pago
    "HU-08", "HU-40", "HU-09", "HU-25",
    # Fase 7: Comprobantes y QR
    "HU-34", "HU-35", "HU-46", "HU-63", "HU-10",
    # Fase 8: Mis Compras y Cancelación
    "HU-36", "HU-58", "HU-12", "HU-54", "HU-57", "HU-55", "HU-61"
]

hus_dict = {h[0]: h for h in HUs}
t_user = doc.add_table(rows=1, cols=5)
for i, h in enumerate(headers_gen):
    t_user.rows[0].cells[i].paragraphs[0].text = h
for c in codigos_user:
    if c in hus_dict:
        row = t_user.add_row()
        for i in range(5):
            row.cells[i].paragraphs[0].text = hus_dict[c][i]
apply_table_styles(t_user, col_widths_general)

doc.add_paragraph() # Espaciador

# --- 5.2 Flujo Socio Cine + ---
h3_socio = doc.add_paragraph()
r = h3_socio.add_run("5.2 Flujo del Socio Cine + (Fidelización, Puntos Divididos y Canjes Proporcionales)")
r.font.name = "Arial"
r.font.size = Pt(12)
r.font.bold = True
r.font.color.rgb = RGBColor(139, 30, 45)

p_socio_desc = doc.add_paragraph()
r = p_socio_desc.add_run(
    "Flujo del cliente afiliado al programa de fidelización: acumula puntos independientes por cine y dulcería, "
    "monitorea sus saldos en la barra de navegación y perfil, y canjea 50 puntos por exactamente 1 entrada sin distorsionar el cobro de entradas adicionales."
)
r.font.name = "Arial"
r.font.size = Pt(9)

codigos_socio = ["HU-14", "HU-49", "HU-16", "HU-50", "HU-15", "HU-56"]
t_socio = doc.add_table(rows=1, cols=5)
for i, h in enumerate(headers_gen):
    t_socio.rows[0].cells[i].paragraphs[0].text = h
for c in codigos_socio:
    if c in hus_dict:
        row = t_socio.add_row()
        for i in range(5):
            row.cells[i].paragraphs[0].text = hus_dict[c][i]
apply_table_styles(t_socio, col_widths_general)

doc.add_paragraph() # Espaciador

# --- 5.3 Flujo Empleado de Ventanilla y Cajero POS ---
h3_emp = doc.add_paragraph()
r = h3_emp.add_run("5.3 Flujo de Empleado de Ventanilla y Cajero (Venta Presencial y Terminal POS)")
r.font.name = "Arial"
r.font.size = Pt(12)
r.font.bold = True
r.font.color.rgb = RGBColor(139, 30, 45)

p_emp_desc = doc.add_paragraph()
r = p_emp_desc.add_run(
    "Flujo del personal de atención presencial: afilia nuevos socios en ventanilla, atiende venta física de boletos en boletería "
    "y despacha consumos en mostrador mediante el terminal táctil reactivo POS con sincronización de inventario en vivo."
)
r.font.name = "Arial"
r.font.size = Pt(9)

codigos_emp = ["HU-17", "HU-18", "HU-19", "HU-26", "HU-27", "HU-28", "HU-29", "HU-38"]
t_emp = doc.add_table(rows=1, cols=5)
for i, h in enumerate(headers_gen):
    t_emp.rows[0].cells[i].paragraphs[0].text = h
for c in codigos_emp:
    if c in hus_dict:
        row = t_emp.add_row()
        for i in range(5):
            row.cells[i].paragraphs[0].text = hus_dict[c][i]
apply_table_styles(t_emp, col_widths_general)

doc.add_paragraph() # Espaciador

# --- 5.4 Flujo Gerente / Administrador ---
h3_admin = doc.add_paragraph()
r = h3_admin.add_run("5.4 Flujo de Gerente / Administrador (Gestión de Sedes, Programación, POS y Analítica)")
r.font.name = "Arial"
r.font.size = Pt(12)
r.font.bold = True
r.font.color.rgb = RGBColor(139, 30, 45)

p_admin_desc = doc.add_paragraph()
r = p_admin_desc.add_run(
    "Flujo del administrador del sistema: administra las Sedes del cine, cataloga películas, programa funciones con mapa visual de horarios ocupados en rojo y validación anticolisión, "
    "controla el stock de dulcería, administra métodos de pago y cupones, gestiona usuarios y roles, supervisa y opera el POS desde accesos directos en el dashboard, y consulta reportes de ventas."
)
r.font.name = "Arial"
r.font.size = Pt(9)

codigos_admin = ["HU-51", "HU-20", "HU-21", "HU-47", "HU-60", "HU-43", "HU-33", "HU-42", "HU-39", "HU-45", "HU-48", "HU-53", "HU-62", "HU-22"]
t_admin = doc.add_table(rows=1, cols=5)
for i, h in enumerate(headers_gen):
    t_admin.rows[0].cells[i].paragraphs[0].text = h
for c in codigos_admin:
    if c in hus_dict:
        row = t_admin.add_row()
        for i in range(5):
            row.cells[i].paragraphs[0].text = hus_dict[c][i]
apply_table_styles(t_admin, col_widths_general)

doc.add_paragraph() # Espaciador

# 3. MATRIZ DE REGLAS DE NEGOCIO (RN01 a RN65)
h3_rn = doc.add_paragraph()
r = h3_rn.add_run("6. Matriz Completa de Reglas de Negocio del Sistema (RN01 a RN65)")
r.font.name = "Arial"
r.font.size = Pt(15)
r.font.bold = True
r.font.color.rgb = RGBColor(93, 20, 33)

p_rn_desc = doc.add_paragraph()
r = p_rn_desc.add_run(
    "A continuación se detallan todas las Reglas de Negocio que rigen la operación, validaciones técnicas, "
    "políticas comerciales y flujos de datos en el sistema Cine+, vinculadas de forma cruzada a cada una de las Historias de Usuario."
)
r.font.name = "Arial"
r.font.size = Pt(9.5)

col_widths_rn = [Inches(1.2), Inches(8.2)]
t_rn = doc.add_table(rows=1, cols=2)
t_rn.rows[0].cells[0].paragraphs[0].text = "Código"
t_rn.rows[0].cells[1].paragraphs[0].text = "Descripción de la Regla de Negocio"

for rn in RNs:
    row = t_rn.add_row()
    row.cells[0].paragraphs[0].text = rn[0]
    row.cells[1].paragraphs[0].text = rn[1]

apply_table_styles(t_rn, col_widths_rn)

# Guardar documento
doc.save("Historias_de_Usuario_CinePlus.docx")
print("Documento guardado con éxito como Historias_de_Usuario_CinePlus.docx")
