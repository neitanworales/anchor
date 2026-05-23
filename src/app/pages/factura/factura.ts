import { ChangeDetectorRef, Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, NgForm } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';
import { FacturaDao } from '../../core/api/dao/FacturaDao';
import { Utils } from '../../core/api/Utils';

@Component({
  selector: 'app-factura',
  imports: [CommonModule, FormsModule],
  templateUrl: './factura.html',
  styleUrl: './factura.css',
  providers: [FacturaDao, Utils]
})
export class Factura {
  isDragOver = false;
  isSaving = false;
  errorMessage = '';
  successMessage = '';
  fileName = '';
  xmlRaw = '';
  formSubmitted = false;

  form = {
    uuid: '',
    versionCfdi: '',
    serie: '',
    folio: '',
    fechaEmision: '',
    fechaTimbrado: '',
    tipoComprobante: '',
    moneda: '',
    tipoCambio: '',
    subtotal: '',
    descuento: '',
    total: '',
    metodoPago: '',
    formaPago: '',
    lugarExpedicion: '',
    exportacion: '',
    rfcEmisor: '',
    nombreEmisor: '',
    regimenEmisor: '',
    rfcReceptor: '',
    nombreReceptor: '',
    domicilioFiscalReceptor: '',
    regimenReceptor: '',
    usoCfdi: '',
    selloCfd: '',
    noCertificado: '',
    certificado: '',
    selloSat: '',
    noCertificadoSat: '',
    rfcPac: '',
    rutaXml: '',
    estatusSat: 'NO_CONSULTADO',
  };

  constructor(
    private facturaDao: FacturaDao,
    private cdr: ChangeDetectorRef
  ) {}

  onFilePicked(event: Event): void {
    const input = event.target as HTMLInputElement;
    if (!input.files || input.files.length === 0) {
      return;
    }
    this.loadXmlFile(input.files[0]);
  }

  onDrop(event: DragEvent): void {
    event.preventDefault();
    this.isDragOver = false;
    if (!event.dataTransfer || event.dataTransfer.files.length === 0) {
      return;
    }
    this.loadXmlFile(event.dataTransfer.files[0]);
  }

  onDragOver(event: DragEvent): void {
    event.preventDefault();
    this.isDragOver = true;
  }

  onDragLeave(event: DragEvent): void {
    event.preventDefault();
    this.isDragOver = false;
  }

  private loadXmlFile(file: File): void {
    this.errorMessage = '';
    this.successMessage = '';
    this.fileName = file.name;

    const reader = new FileReader();
    reader.onload = () => {
      const content = String(reader.result || '');
      this.xmlRaw = content;
      this.applyXmlData(content);
      this.cdr.detectChanges();
    };
    reader.onerror = () => {
      this.errorMessage = 'No se pudo leer el archivo XML.';
    };
    reader.readAsText(file);
  }

  private applyXmlData(xmlText: string): void {
    try {
      const parser = new DOMParser();
      const xml = parser.parseFromString(xmlText, 'text/xml');

      const comprobante = xml.getElementsByTagName('cfdi:Comprobante')[0]
        || xml.getElementsByTagName('Comprobante')[0];
      const emisor = xml.getElementsByTagName('cfdi:Emisor')[0]
        || xml.getElementsByTagName('Emisor')[0];
      const receptor = xml.getElementsByTagName('cfdi:Receptor')[0]
        || xml.getElementsByTagName('Receptor')[0];
      const timbre = xml.getElementsByTagName('tfd:TimbreFiscalDigital')[0]
        || xml.getElementsByTagName('TimbreFiscalDigital')[0];

      if (comprobante) {
        this.form.versionCfdi = comprobante.getAttribute('Version') || comprobante.getAttribute('version') || '';
        this.form.serie = comprobante.getAttribute('Serie') || '';
        this.form.folio = comprobante.getAttribute('Folio') || '';
        this.form.fechaEmision = comprobante.getAttribute('Fecha') || '';
        this.form.tipoComprobante = comprobante.getAttribute('TipoDeComprobante') || '';
        this.form.moneda = comprobante.getAttribute('Moneda') || '';
        this.form.tipoCambio = comprobante.getAttribute('TipoCambio') || '';
        this.form.subtotal = comprobante.getAttribute('SubTotal') || '';
        this.form.descuento = comprobante.getAttribute('Descuento') || '';
        this.form.total = comprobante.getAttribute('Total') || '';
        this.form.metodoPago = comprobante.getAttribute('MetodoPago') || '';
        this.form.formaPago = comprobante.getAttribute('FormaPago') || '';
        this.form.lugarExpedicion = comprobante.getAttribute('LugarExpedicion') || '';
        this.form.exportacion = comprobante.getAttribute('Exportacion') || '';
        this.form.selloCfd = comprobante.getAttribute('Sello') || '';
        this.form.noCertificado = comprobante.getAttribute('NoCertificado') || '';
        this.form.certificado = comprobante.getAttribute('Certificado') || '';
      }

      if (emisor) {
        this.form.rfcEmisor = emisor.getAttribute('Rfc') || '';
        this.form.nombreEmisor = emisor.getAttribute('Nombre') || '';
        this.form.regimenEmisor = emisor.getAttribute('RegimenFiscal') || '';
      }

      if (receptor) {
        this.form.rfcReceptor = receptor.getAttribute('Rfc') || '';
        this.form.nombreReceptor = receptor.getAttribute('Nombre') || '';
        this.form.domicilioFiscalReceptor = receptor.getAttribute('DomicilioFiscalReceptor') || '';
        this.form.regimenReceptor = receptor.getAttribute('RegimenFiscalReceptor') || '';
        this.form.usoCfdi = receptor.getAttribute('UsoCFDI') || '';
      }

      if (timbre) {
        this.form.uuid = timbre.getAttribute('UUID') || '';
        this.form.fechaTimbrado = timbre.getAttribute('FechaTimbrado') || '';
        this.form.selloSat = timbre.getAttribute('SelloSAT') || '';
        this.form.noCertificadoSat = timbre.getAttribute('NoCertificadoSAT') || '';
        this.form.rfcPac = timbre.getAttribute('RfcProvCertif') || '';
      }

      this.successMessage = 'XML cargado y campos actualizados.';
    } catch {
      this.errorMessage = 'El XML no tiene un formato valido de CFDI.';
    }
  }

  isCfdi4(): boolean {
    return (this.form.versionCfdi || '').trim().startsWith('4');
  }

  isTipoComprobantePago(): boolean {
    return (this.form.tipoComprobante || '').trim().toUpperCase() === 'P';
  }

  isTipoCambioRequired(): boolean {
    const moneda = (this.form.moneda || '').trim().toUpperCase();
    return moneda !== '' && moneda !== 'MXN';
  }

  isFormaMetodoPagoRequired(): boolean {
    return !this.isTipoComprobantePago();
  }

  saveFactura(form?: NgForm): void {
    if (this.isSaving) {
      return;
    }

    this.formSubmitted = true;
    this.errorMessage = '';
    this.successMessage = '';

    if (form && form.invalid) {
      this.errorMessage = 'Completa los campos obligatorios antes de guardar.';
      return;
    }

    const payload: any = {
      ...this.form,
      xmlOriginal: this.xmlRaw || undefined
    };

    this.isSaving = true;
    this.facturaDao.createFactura(payload).subscribe({
      next: () => {
        this.isSaving = false;
        this.successMessage = 'Factura guardada correctamente.';
      },
      error: (err: HttpErrorResponse) => {
        this.isSaving = false;
        this.errorMessage = err.error?.message || 'No se pudo guardar la factura.';
      }
    });
  }

}
