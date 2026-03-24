import { Component, OnInit, ChangeDetectorRef, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { BoletaService } from '../../../core/services/boleta.service';
import { Subject } from 'rxjs';
import { debounceTime } from 'rxjs/operators';
import { Router, ActivatedRoute } from '@angular/router';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-generar-boleta',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './generar-boleta.component.html'
})
export class GenerarBoletaComponent implements OnInit {
  periodoActual: string = '';
  categorias: any[] = [];
  empleados: any[] = [];
  resumen: any = null;
  isLoading = signal(true);
  isCalculating = signal(false);
  isSaving = signal(false);
  boletaId: number | null = null;
  empleadosOriginales: any[] = []; // Para detectar bajas por omisión
  
  // Paginación
  paginaActual = 1;
  empleadosPorPagina = 10;
  Math = Math;

  private calcSubject = new Subject<void>();

  constructor(
    private boletaService: BoletaService,
    private router: Router,
    private route: ActivatedRoute,
    private cdr: ChangeDetectorRef
  ) { }

  ngOnInit() {
    this.cargarCategorias();

    // Generar periodo actual YYYYMM
    const date = new Date();
    const year = date.getFullYear();
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    this.periodoActual = `${year}${month}`;

    this.calcSubject.pipe(
      debounceTime(500)
    ).subscribe(() => {
      this.ejecutarCalculoEnBackend();
    });

    this.route.paramMap.subscribe(params => {
      const id = params.get('id');
      if (id) {
        this.boletaId = +id;
        this.cargarBoletaBorrador(this.boletaId);
      } else {
        this.cargarEmpleadosActivos();
      }
    });
  }

  cargarBoletaBorrador(id: number) {
    this.isLoading.set(true);
    this.boletaService.getBoleta(id).subscribe({
      next: (res) => {
        if (res.data && res.data.empleados) {
          this.empleados = res.data.empleados;
          this.periodoActual = res.data.periodo; // Cargar el periodo del borrador
          this.recalcular();
        }
        this.isLoading.set(false);
        this.cdr.detectChanges();
      },
      error: (err) => {
        console.error(err);
        Swal.fire('Error', 'No se pudo cargar el borrador o no existe.', 'error');
        this.isLoading.set(false);
        this.cdr.detectChanges();
        this.router.navigate(['/app/boletas']);
      }
    });
  }

  cargarEmpleadosActivos() {
    this.isLoading.set(true);
    this.boletaService.getEmpleadosActivos().subscribe({
      next: (res) => {
        if (res.status === 'success') {
          // Filtrar solo los empleados activos (estado_baja == 0)
          const activos = (res.data || []).filter((e: any) => e.estado_baja === 0);
          this.empleadosOriginales = JSON.parse(JSON.stringify(activos)); // Backup para comparar ausencias
          
          this.empleados = activos.map((e: any) => ({
            cuil: e.cuil,
            nombre: e.nombre,
            categoria_id: e.categoria_id,
            fecha_ingreso: e.fecha_ingreso || '',
            fecha_egreso: e.fecha_egreso || '',
            importe_remunerativo: e.importe_remunerativo || null,
            importe_no_remunerativo: e.importe_no_remunerativo || null
          }));

          if (this.empleados.length > 0) {
              this.recalcular();
          }
        }
        this.isLoading.set(false);
        this.cdr.detectChanges();
      },
      error: (err) => {
        console.error('Error cargando empleados activos', err);
        this.isLoading.set(false);
        this.cdr.detectChanges();
      }
    });
  }

  cargarCategorias() {
    this.boletaService.getCategorias().subscribe({
      next: (res) => {
        if (res.status === 'success') {
          this.categorias = res.data;
        }
      },
      error: (err) => console.error('Error al cargar categorias maestro: ', err)
    });
  }

  agregarEmpleado() {
    this.empleados.push({
      cuil: '',
      nombre: '',
      categoria_id: null, // Forzar selección manual
      fecha_ingreso: '',
      fecha_egreso: '',
      importe_remunerativo: null,
      importe_no_remunerativo: null
    });
    this.paginaActual = this.totalPaginas;
    this.recalcular();
  }

  eliminarEmpleado(index: number) {
    this.empleados.splice(index, 1);
    
    // Si la pagina actual se quedó vacía y no es la primera, volvemos atrás
    if (this.empleadosPaginados.length === 0 && this.paginaActual > 1) {
      this.paginaActual--;
    }
    
    this.recalcular();
  }

  get empleadosPaginados() {
    const inicio = (this.paginaActual - 1) * this.empleadosPorPagina;
    return this.empleados.slice(inicio, inicio + this.empleadosPorPagina);
  }

  get totalPaginas() {
    return Math.max(1, Math.ceil(this.empleados.length / this.empleadosPorPagina));
  }

  cambiarPagina(delta: number) {
    this.paginaActual += delta;
  }

  recalcular() {
    this.isCalculating.set(true);
    this.calcSubject.next();
  }

  esFormularioValido(): boolean {
    if (this.empleados.length === 0) return false;
    for (let emp of this.empleados) {
      if (!emp.cuil || !this.isValidCuil(String(emp.cuil))) return false;
      if (!emp.nombre || emp.nombre.trim() === '') return false;
      if (!emp.fecha_ingreso || !this.isFechaIngresoValida(emp.fecha_ingreso)) return false;
      if (emp.fecha_egreso && !this.isFechaEgresoValida(emp.fecha_ingreso, emp.fecha_egreso)) return false;
      if (emp.importe_remunerativo === null || emp.importe_remunerativo === undefined || emp.importe_remunerativo === '' || emp.importe_remunerativo <= 0) return false;
      if (emp.importe_no_remunerativo !== null && emp.importe_no_remunerativo !== undefined && emp.importe_no_remunerativo !== '') {
          if (emp.importe_no_remunerativo < 0) return false;
      }
    }
    return true;
  }

  isValidCuil(cuil: any): boolean {
    if (!cuil) return false;
    let strCuit = String(cuil).replace(/[-\s_]/g, '');
    if (strCuit.length !== 11 || !/^\d+$/.test(strCuit)) return false;

    const multiplicadores = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
    let suma = 0;
    
    for (let i = 0; i < 10; i++) {
        suma += parseInt(strCuit.charAt(i), 10) * multiplicadores[i];
    }
    
    const resto = suma % 11;
    let digitoEsperado = 11 - resto;
    if (digitoEsperado === 11) digitoEsperado = 0;
    else if (digitoEsperado === 10) digitoEsperado = 9;

    return digitoEsperado === parseInt(strCuit.charAt(10), 10);
  }

  isFechaIngresoValida(fecha_ingreso: string): boolean {
    if (!fecha_ingreso) return false;
    const parts = fecha_ingreso.split('-');
    if (parts.length < 2) return false;
    const reqPeriodo = parseInt(`${parts[0]}${parts[1]}`, 10);
    const currPeriodo = parseInt(this.periodoActual, 10);
    return reqPeriodo <= currPeriodo;
  }

  isFechaEgresoValida(fecha_ingreso: string, fecha_egreso: string): boolean {
    if (!fecha_egreso) return true; // Optional
    if (!fecha_ingreso) return false;

    const fEgreso = new Date(fecha_egreso + 'T00:00:00');
    const fIngreso = new Date(fecha_ingreso + 'T00:00:00');
    const today = new Date();
    today.setHours(23, 59, 59, 999);

    if (fEgreso < fIngreso) return false;
    if (fEgreso > today) return false;

    return true;
  }

  formatDateForDisplay(dateStr: string | null | undefined): string {
    if (!dateStr) return '';
    const parts = dateStr.split('-');
    if (parts.length !== 3) return dateStr;
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
  }

  buscarDatosPorCuil(emp: any) {
    if (!emp.cuil || !this.isValidCuil(String(emp.cuil))) return;
    
    let cuilStr = String(emp.cuil).replace(/[-\s_]/g, '');
    this.boletaService.getPersonaByCuil(cuilStr).subscribe({
      next: (res) => {
        if (res.status === 'success' && res.data) {
          if (res.data.nombre) emp.nombre = res.data.nombre;
          if (res.data.es_empleado) {
            if (res.data.fecha_ingreso) emp.fecha_ingreso = res.data.fecha_ingreso;
            if (res.data.categoria_id) emp.categoria_id = res.data.categoria_id;
          }
          this.recalcular();
          this.cdr.detectChanges();
        }
      },
      error: (err) => {
          console.debug('Cuil no encontrado para autocompletado.', err);
      }
    });
  }

  onCuilChange(empleado: any, newValue: string) {
    if (newValue) {
      empleado.cuil = newValue.toString().replace(/[^0-9]/g, '');
    } else {
      empleado.cuil = '';
    }
    this.recalcular();
  }

  ejecutarCalculoEnBackend() {
    if (this.empleados.length === 0) {
        this.resumen = null;
        this.isCalculating.set(false);
        this.cdr.detectChanges();
        return;
    }

    const payload = {
      periodo: this.periodoActual,
      empleados: this.empleados
    };

    this.boletaService.calcularBoleta(payload).subscribe({
      next: (res) => {
        if (res.status === 'success') {
          this.resumen = res.data;
        }
        this.isCalculating.set(false);
        this.cdr.detectChanges();
      },
      error: (err) => {
        console.error('Error calculando boleta', err);
        this.isCalculating.set(false);
        this.cdr.detectChanges();
      }
    });
  }

  generarBoleta(isDraft: boolean = false) {
    if (!isDraft && !this.esFormularioValido()) {
      Swal.fire('Advertencia', 'Por favor, complete todos los campos requeridos de los empleados antes de finalizar la declaración.', 'warning');
      return;
    }

    if (isDraft && this.empleados.length === 0) {
      Swal.fire('Advertencia', 'Agregue al menos un empleado antes de guardar el borrador.', 'warning');
      return;
    }

    if (!isDraft) {
        const cuilsActuales = this.empleados.map(e => String(e.cuil).replace(/[-\s_]/g, ''));
        const empleadosAusentes = this.empleadosOriginales.filter(eo => {
            const cuilOrig = String(eo.cuil).replace(/[-\s_]/g, '');
            return !cuilsActuales.includes(cuilOrig);
        });

        if (empleadosAusentes.length > 0) {
            const nombres = empleadosAusentes.map(e => `<li>${e.nombre} (CUIL: ${e.cuil})</li>`).join('');
            Swal.fire({
                title: 'Bajas Automáticas Detectadas',
                html: `<p>Los siguientes empleados no están en la boleta actual. <b>Serán dados de baja</b> automáticamente (fecha arbitraria: último día del mes anterior):</p><ul style="text-align:left; color:#d33; font-size: 0.9em; max-height: 150px; overflow-y: auto;">${nombres}</ul><p>¿Deseas generar la boleta y aplicar las bajas?</p>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, generar y dar de baja',
                cancelButtonText: 'Revisar nómina'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.enviarBoleta(isDraft);
                }
            });
            return;
        }
    }

    this.enviarBoleta(isDraft);
  }

  private enviarBoleta(isDraft: boolean) {
    this.isSaving.set(true);
    const payload = {
      periodo: this.periodoActual,
      empleados: this.empleados,
      is_draft: isDraft,
      boleta_id: this.boletaId
    };

    this.boletaService.generarBoleta(payload).subscribe({
      next: (res) => {
        this.isSaving.set(false);
        
        let msg = isDraft ? 'Borrador guardado exitosamente.' : `Boleta #${res.data.boleta_id} generada y lista para pago.`;
        if (isDraft) {
            Swal.fire({
              icon: 'success',
              title: 'Borrador',
              text: msg,
              confirmButtonColor: '#0E7A44'
            }).then(() => {
              this.router.navigate(['/app/boletas']);
            });
        } else {
            Swal.fire({
              icon: 'success',
              title: '¡Generación Exitosa!',
              text: msg,
              showDenyButton: true,
              confirmButtonText: 'Ir al Listado',
              denyButtonText: '<i class="fas fa-file-pdf"></i> Descargar PDF',
              confirmButtonColor: '#0E7A44',
              denyButtonColor: '#3085d6'
            }).then((result) => {
              if (result.isDenied) {
                  this.descargarBoletaPdf(res.data.boleta_id);
              } else {
                  this.router.navigate(['/app/boletas']);
              }
            });
        }
      },
      error: (err) => {
        this.isSaving.set(false);
        console.error(err);
        Swal.fire('Error', 'Hubo un problema al procesar la boleta.', 'error');
      }
    });
  }

  descargarBoletaPdf(id: number) {
    Swal.fire({
      title: 'Generando PDF',
      text: 'Por favor espere...',
      allowOutsideClick: false,
      didOpen: () => { Swal.showLoading(); }
    });

    this.boletaService.descargarBoletaPdf(id).subscribe({
      next: (blob) => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `boleta_${id}.pdf`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        Swal.close();
        this.router.navigate(['/app/boletas']);
      },
      error: (err) => {
        console.error(err);
        Swal.fire('Error', 'No se pudo generar el reporte. Verifique que Jasper esté configurado.', 'error').then(() => {
            this.router.navigate(['/app/boletas']);
        });
      }
    });
  }
}
