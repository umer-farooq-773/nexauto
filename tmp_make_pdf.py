from reportlab.lib import colors
from reportlab.lib.pagesizes import letter, landscape
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.enums import TA_CENTER
from reportlab.lib.units import inch
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak, KeepTogether
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.pdfbase import pdfmetrics
from reportlab.lib.colors import HexColor

OUT='output/pdf/champs-vma-motive-product-comparison.pdf'
NAVY=HexColor('#15324B'); TEAL=HexColor('#087E8B'); LIGHT=HexColor('#EFF5F7'); MID=HexColor('#D8E4E8'); DARK=HexColor('#20323D'); MUTED=HexColor('#5A6B75'); AMBER=HexColor('#FFF3D8')
styles=getSampleStyleSheet()
styles.add(ParagraphStyle(name='CoverTitle',parent=styles['Title'],fontName='Helvetica-Bold',fontSize=25,leading=30,textColor=NAVY,spaceAfter=12))
styles.add(ParagraphStyle(name='Sub',parent=styles['Normal'],fontSize=12,leading=18,textColor=MUTED,spaceAfter=10))
styles.add(ParagraphStyle(name='H1x',parent=styles['Heading1'],fontSize=18,leading=22,textColor=NAVY,spaceBefore=4,spaceAfter=10))
styles.add(ParagraphStyle(name='H2x',parent=styles['Heading2'],fontSize=12,leading=15,textColor=TEAL,spaceBefore=7,spaceAfter=5))
styles.add(ParagraphStyle(name='Bodyx',parent=styles['BodyText'],fontSize=9,leading=13,textColor=DARK,spaceAfter=6))
styles.add(ParagraphStyle(name='Smallx',parent=styles['BodyText'],fontSize=7.5,leading=10,textColor=MUTED))
styles.add(ParagraphStyle(name='Cell',parent=styles['BodyText'],fontSize=7.7,leading=10.2,textColor=DARK))
styles.add(ParagraphStyle(name='CellHead',parent=styles['BodyText'],fontSize=7.7,leading=10,textColor=colors.white,fontName='Helvetica-Bold'))
styles.add(ParagraphStyle(name='Callout',parent=styles['BodyText'],fontSize=9.2,leading=13,textColor=NAVY,backColor=LIGHT,borderColor=MID,borderWidth=.5,borderPadding=8,spaceBefore=4,spaceAfter=8))
P=lambda s,style='Cell':Paragraph(s,styles[style])
def table(rows,widths,repeat=1):
    data=[]
    for ri,row in enumerate(rows):
        data.append([P(str(x),'CellHead' if ri==0 else 'Cell') for x in row])
    factor=9.85*inch/sum(widths); widths=[w*factor for w in widths]
    t=Table(data,colWidths=widths,repeatRows=repeat,hAlign='LEFT')
    t.setStyle(TableStyle([('BACKGROUND',(0,0),(-1,0),NAVY),('GRID',(0,0),(-1,-1),.35,MID),('VALIGN',(0,0),(-1,-1),'TOP'),('LEFTPADDING',(0,0),(-1,-1),6),('RIGHTPADDING',(0,0),(-1,-1),6),('TOPPADDING',(0,0),(-1,-1),5),('BOTTOMPADDING',(0,0),(-1,-1),5),('ROWBACKGROUNDS',(0,1),(-1,-1),[colors.white,LIGHT])]))
    return t

def footer(canvas,doc):
    canvas.saveState(); w,h=doc.pagesize
    canvas.setStrokeColor(MID); canvas.line(0.5*inch,0.48*inch,w-0.5*inch,0.48*inch)
    canvas.setFont('Helvetica',7.5); canvas.setFillColor(MUTED)
    canvas.drawString(.55*inch,.30*inch,'CHAMPS VMA | Product comparison | 5 November 2026')
    canvas.drawRightString(w-.55*inch,.30*inch,f'{doc.page}')
    canvas.restoreState()

doc=SimpleDocTemplate(OUT,pagesize=landscape(letter),rightMargin=.55*inch,leftMargin=.55*inch,topMargin=.55*inch,bottomMargin=.65*inch,title='CHAMPS VMA and Motive Product Comparison',author='CHAMPS VMA Product Analysis')
story=[]
# cover / executive
story += [Spacer(1,.30*inch),P('CHAMPS VMA and Motive','CoverTitle'),P('Functional comparison and product direction for CHAMPS VMA','Sub'),P('Prepared 5 November 2026 | Reviewed in the signed-in product interfaces and CHAMPS VMA application source','Smallx'),Spacer(1,.2*inch)]
story += [P('Executive view','H1x'),P('<b>Motive is a broad fleet platform with dedicated device operations.</b> Its Devices area focuses on gateways, cameras, health states, installation and connectivity exceptions, and device reports. <b>CHAMPS VMA is an operations and incident workflow system.</b> It brings assets, drivers, waybills, trips, monitoring, inspections, and operational incident records together. The breathalyser page is a specialized, staff-entered incident record attached to a trip context.','Bodyx'),P('The strongest product opportunity is not to imitate Motive’s hardware catalog. Make CHAMPS VMA the trusted operational system of record, then add a device and data integration layer that can turn telemetry or partner device signals into actionable, auditable workflows.','Callout')]
story += [P('What was compared','H2x'),table([
['Dimension','Motive','CHAMPS VMA','Product implication'],
['Primary job','Fleet visibility, safety/compliance workflows, and management of connected devices.','Manage vehicles, drivers, waybills/trips, inspections, and incident/intervention workflows.','Position VMA around configurable operations and incident resolution, with integrations where customers need telematics.'],
['Device model','First-class device inventory for vehicle gateways and cameras; status, activity, power, firmware, location, installation and camera health.','Assets and live vehicle monitoring are visible; the reviewed breathalyser workflow records an external device number and manual test values. No equivalent gateway/camera inventory was visible in reviewed screens.','Add optional integrations and device inventory only where needed; distinguish reported telemetry from staff-entered evidence.'],
['Operating model','Dashboard, inventory tables, health summaries, exception center, reports and installation/troubleshooting links.','Operational dashboard plus module menus, structured case records, attachments, status management, CSV export, maps and incident tracking.','Preserve VMA’s domain workflows while improving shared navigation, queues, search, audit and reporting.'],
['Evidence basis','Observed app.gomotive.com Devices Overview, Vehicles, Gateways and Cameras screens; tenant showed zero devices/results.','Observed CHAMPS VMA dashboard and Breathaliser Test Management create form/list; repository routes and form source reviewed.','This is an interface-level comparison; zero Motive inventory means device row-level functions could not be inspected.']
],[1.3*inch,3.15*inch,3.15*inch,3.15*inch])]
story += [Spacer(1,7),P('Scope note: the Motive workspace displayed zero devices and empty device tables. Its feature coverage below reflects visible navigation, table schema, dashboard labels and reports; per-device drill-down and actual fleet data were unavailable. No individual breathalyser records or personal test results are reproduced here.','Smallx'),PageBreak()]
# module landscape
story += [P('1. Module map','H1x'),P('This is a functional map of the visible product surfaces, not a claim that every linked area is licensed or enabled for every customer.','Bodyx')]
story += [table([
['Product area','Motive visible modules','CHAMPS VMA visible modules','Relative strength / gap'],
['Core fleet','Fleetview; Safety; Compliance; Fuel; Fleet Cards; Maintenance; Workforce; Coaching; Messages; Documents; Devices; App Marketplace.','Asset Management; Trip Management; Incident & Intervention; Driver Behavior; Goods Inspection; Configuration; Vehicle History; Incident Report; Reports & Analytics; Track Incidents; Monitoring.','Motive exposes a wider suite across safety, finance and workforce. VMA exposes stronger operational case taxonomy in its navigation.'],
['Asset and people','Vehicle/entity list and assignment/group context; devices associate to entity and group.','Asset/vehicle, driver and driver mate management; permissions and role-based menus are represented in source routes.','VMA can deepen lifecycle records and operator assignments; Motive offers connected device attributes.'],
['Trip / shipment operations','Fleetview and integrations implied by broad product menu; device entity pages show vehicle identity/location.','Trip Management and waybill stages; route trip, goods inspection, waybill, incident links are supported in application routes/source.','VMA has a shipment/waybill-oriented operational model that is distinct from a telematics-first console.'],
['Incident / risk','Safety, Coaching, Compliance and Messages are top-level areas; Devices Action Center exposes camera issues.','Incident & Intervention types include Impoundment, Breakdown, Illegal Detention, Accident, Bogged Down; Driver Behavior includes Route Diversion, Illegal Haulage, Breathalyser, Illegal Shortage and Misconduct.','VMA has explicit incident category coverage; consider unified case triage, ownership, SLA and outcomes across those categories.'],
['Monitoring and history','Device status history and device-specific reports; device activity timestamps and last location.','Live Vehicle Map/Monitoring, vehicle history, incident tracking/map and dashboard charts.','VMA already has live operations surfaces; connected-device event history is the notable opportunity.'],
['Administration','Company/admin entry, group filtering and notifications visible.','Configuration; users, roles/permissions, settings, notifications and role-sensitive route middleware in source.','For a product, improve tenant setup, role templates and guided configuration before adding breadth.']
],[1.12*inch,3.1*inch,3.55*inch,2.98*inch])]
story += [P('Observed VMA dashboard signals','H2x'),P('The dashboard surfaces total assets, total drivers, total incidents and ongoing trips, plus a live map, online/idle/moving/offline counts, completed trips versus incident trend, and waybill status distribution. This gives operators a useful broad overview. The next step is to connect those high-level indicators to prioritized queues and clear next actions.','Bodyx'),PageBreak()]
# device capability matrix
story += [P('2. Device and monitoring capabilities','H1x'),P('The product difference is largely about data origin and operational response: Motive monitors device state continuously; VMA records operational events and evidence through user workflows, alongside live tracking.','Bodyx')]
story += [table([
['Feature','Motive Devices area','CHAMPS VMA reviewed scope','VMA product opportunity'],
['Device inventory','Separate Vehicles, Gateways and Cameras views; tables include identity/type, vehicle/entity, location, group, serial, firmware, power/activity and status.','Assets/vehicles exist in the application; breathalyser form records a free-form device number. No gateway/camera inventory appeared in the reviewed VMA menus.','Create an optional Device Registry: type, serial, linked asset, vendor, install date, firmware, last check-in, owner and lifecycle.'],
['Health state','Overview status counts: Normal, Powered off, Install issue, Other issues, Return device, Setup incomplete; gateways and cameras summarized separately.','VMA shows vehicle states Online/Idle/Moving/Offline in dashboard monitoring. The breathalyser record has business result and case status, rather than online device health.','Keep telematics health separate from incident result; expose stale/offline state and data freshness transparently.'],
['Camera diagnostics','Action Center links for obstruction, misalignment, disconnected/powered off, not recording and not communicating.','No camera diagnostic center was visible in the reviewed VMA pages.','If video hardware is in scope, ingest vendor alerts and open an incident with asset, timestamp, location and evidence link.'],
['Location and recency','Device tables show last location/date/geofence and last activity; overview can be filtered by groups.','Live map shows vehicles; monitoring and vehicle history links are visible.','Show last telemetry time, map trail, geofence context and source so “live” has an explicit freshness threshold.'],
['Filtering and fleet scale','Group selector, search, sorting and additional filters; camera filters include geofence/type.','Breathalyser list offers search and sortable columns; asset/driver/waybill selection in create form.','Add reusable filters, saved views, bulk assignment and role-aware queues across incidents and assets.'],
['Reports','Device status report, device status history, gateways detail, cameras detail, dashcam connection history; installation and troubleshooting guides.','Breathalyser CSV export; reports & analytics and incident reports are surfaced; source includes CSV export routes.','Add scheduled reports, export templates and device-to-incident trend analysis.'],
['Actions / resolution','Exception links route directly to relevant camera health-status filter; guides support installation/troubleshooting.','Breathalyser flow records action taken, remarks, attachments and Open/Under Review/Closed status. Other incident detail pages support status changes and incident chat.','Build a common case queue: assign, acknowledge, investigate, act, resolve, audit. Link telemetry alerts to the same workflow.'],
['Integrations','Motive hardware is central; App Marketplace and device installation support are visible.','Source includes Wialon report/map integration and application routes for map/report generation.','Productize integrations behind a provider adapter; normalize identifiers, timestamps, location and event provenance.']
],[1.25*inch,3.0*inch,3.35*inch,3.15*inch])]
story += [Spacer(1,8),P('Motive’s Devices Overview showed zero devices in this tenant and the inventory pages showed no results. The table schema and overview still provide strong evidence of the intended management model, but not of real-world alert volume, device-specific controls or latency.','Smallx'),PageBreak()]
# Breathalyser deep dive
story += [P('3. Breathalyser Test Management deep dive','H1x'),P('The module is a structured case ledger associated with a trip/waybill, asset and driver. The page supports creating a record, searching/sorting the log, viewing a record, attaching evidence and exporting CSV.','Bodyx')]
story += [table([
['Workflow stage','Observed behavior','Product assessment'],
['Start record','Add Breathaliser Test opens a create form; Asset, Driver, Waybill and Test Date are required. Source form warns that active waybills are needed and can flag when a waybill is not In Transit.','Good operational context: the event is tied to a specific assignment rather than being a standalone note. Make eligibility and missing prerequisites explicit before the form opens.'],
['Capture event','Form includes location search, device number, BAC Level, Test Result (Negative/Borderline/Positive), Status (Open/Under Review/Closed), Action Taken, required Remarks and evidence upload.','Useful domain fields; standardize units, test method/device metadata, timestamp/timezone, operator and evidence verification. Avoid implying a universal legal threshold.'],
['Review case','List columns include waybill, driver, asset, test date, BAC, result, attachment, status and action menu. Detail view/actions exist; status workflow has Open, Under Review and Closed.','Add explicit reviewer/assignee, due date, disposition, immutable timeline, reason for status transition and notification. Keep sensitive fields permission-gated.'],
['Resolve and learn','Action Taken, Remarks, file attachment and statuses support a basic resolution trail. Dashboard has broad incident counts, but no breathalyser analytics were established from the view.','Connect case closure to follow-up tasks, policy acknowledgement, repeat-test scheduling if configured, and privacy-preserving trend views.'],
['Export / audit','CSV export button and file links are visible. The repository has export routes and role/permission controls.','Add export scope confirmation, audit log, redaction options and export history; minimize exposure of sensitive values in broad reports.']
],[1.2*inch,4.2*inch,5.35*inch])]
story += [P('Proposed case record minimum','H2x'),table([
['Identity and context','Event and measurement','Workflow and evidence'],
['Case ID; tenant; linked asset/driver/waybill; operator who entered record.','Test timestamp with timezone; location; device make/model/serial; numeric value and explicit unit; result; method and calibration/verification metadata.','Status history; assignee/reviewer; action taken; reason/disposition; notes; attachment metadata and integrity; audit events; access classification.']
],[3.5*inch,3.6*inch,3.65*inch]),Spacer(1,6),P('Privacy and safety product requirement: breathalyser readings are sensitive personal information. Apply least-privilege access, strong auditability, retention rules configurable by customer policy, masked list views where practical, and deliberate controls on exports. Keep business workflow status distinct from the underlying test result.','Callout'),PageBreak()]
# Product strategy
story += [P('4. Product gaps and recommended sequence','H1x'),P('CHAMPS VMA already has a strong operational foundation. The recommendation is to make its workflows coherent and product-ready first, then add device ingestion as a layer that feeds those workflows.','Bodyx')]
story += [table([
['Priority','Build / improve','Why it matters','Practical acceptance signal'],
['P0 Product foundation','Tenant onboarding; consistent role templates; organization/group model; searchable navigation; consistent status definitions; help/onboarding.','A product must be configurable and understandable across customers, not only one current setup.','A new tenant can create its organization, add assets/users, configure roles and complete a first incident without administrator intervention.'],
['P0 Case operations','Unified incident queue across incident types; ownership, priority, due time, activity timeline, comments/chat, evidence and resolution; saved filters.','Today the menu has multiple distinct records. Common handling mechanics make it easier to operate without erasing domain-specific forms.','An operator can triage, assign, investigate and close cases across categories with a complete history.'],
['P0 Data quality','Required-field rules, consistent identifiers, validation, duplicate detection, file hygiene, timestamps/timezones and structured action/disposition.','The reviewed breathalyser table visibly includes missing links/values in some rows, suggesting data completeness and linkage deserve attention.','Completeness metrics, fewer unlinked cases, clear missing-data warnings, validated attachment metadata.'],
['P1 Monitoring and alerts','Freshness-aware map/status; configurable alert rules for offline assets, geofence and trip events; notification routing and acknowledgement.','VMA already exposes live map and online states; make them actionable with clear thresholds and resolution.','Each alert shows source, observed time, confidence/freshness, assignee and resolution.'],
['P1 Device integrations','Provider-neutral device registry and adapters; import gateway/camera identity and health; link device alerts to asset and incident.','Motive’s device controls are a benchmark, while VMA’s existing Wialon-related source is a practical integration foundation.','One connected provider can onboard devices, reconcile assets, ingest status and create traceable alerts.'],
['P1 Reporting','Operational dashboards by time/customer/group; case aging, incident rate, trip outcomes, asset utilization; scheduled and auditable exports.','Current dashboards provide counts and trends; product customers need drill-down and decision support.','Every chart links to a filtered list; report definitions and export access are documented.'],
['P2 Mobile / field workflow','Responsive field capture with offline draft, photo/file evidence, location and quick incident submission.','Incidents and tests often occur away from a desk; reduce delayed/manual transcription.','Field user records a case with context; sync conflicts and uploads are visible and recoverable.'],
['P2 Integrations marketplace','Documented API/webhooks and partner connectors for telematics, identity, messaging and customer systems.','Extends coverage without building every hardware/product function in-house.','Versioned API, scoped credentials, delivery logs, retries and customer-managed integration settings.']
],[.65*inch,2.55*inch,3.4*inch,4.15*inch])]
story += [PageBreak(),P('Recommended product positioning','H2x'),P('<b>CHAMPS VMA: Fleet operations and incident management for organizations that need trip-linked accountability.</b> Differentiate through waybill-aware operational workflows, configurable incident categories, inspection and evidence handling, regional/customer-specific processes, and cross-module traceability. Offer telematics as an integration capability, not as an unqualified claim of native hardware parity.','Callout')]
story += [P('5. Evidence and limitations','H1x'),P('Sources reviewed','H2x'),P('Motive signed-in interface: <link href="https://app.gomotive.com/en-US/#/devices/overview" color="#087E8B">Devices Overview</link>, <link href="https://app.gomotive.com/en-US/#/devices/entities" color="#087E8B">Vehicles</link>, <link href="https://app.gomotive.com/en-US/#/devices/gateways" color="#087E8B">Gateways</link> and <link href="https://app.gomotive.com/en-US/#/devices/cameras" color="#087E8B">Cameras</link>. Visible global navigation also included Fleetview, Safety, Compliance, Fuel, Fleet Cards, Maintenance, Workforce, Coaching, Messages, Documents and App Marketplace.','Bodyx'),P('CHAMPS VMA signed-in interface: dashboard and <link href="https://vma.champsconsults.com/my-account/incident-intervention/breathaliser-test" color="#087E8B">Breathaliser Test Management</link>, including the create form and visible table schema. Local source review: <font name="Helvetica-Bold">routes/panel.php</font>; breathaliser test form/list views and component; app route/controller references for assets, trips, incidents, maps and exports.','Bodyx'),P('Limitations','H2x'),P('This comparison covers what was visible in the authenticated accounts and application source at review time. Motive’s tenant had no device records; device drill-downs, active alerts with live data, control actions and actual service entitlements could not be verified. CHAMPS VMA’s breadth was mapped from visible navigation and repository routes; not every VMA page was opened or every permission role exercised. Statements about product opportunities are recommendations, not claims about existing capabilities.','Bodyx'),P('No individual driver/test records, names, BAC values, attachment names or case identifiers are included. Recommendations touching sensitive test data should be reviewed against the customer’s jurisdiction, policy, and privacy requirements.','Bodyx')]
doc.build(story,onFirstPage=footer,onLaterPages=footer)
print(OUT)






