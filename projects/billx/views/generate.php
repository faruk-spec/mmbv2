<?php
/** @var array $config @var array $user @var string|null $error */
$csrfToken = \Core\Security::generateCsrfToken();
$selectedType = htmlspecialchars($_GET['type'] ?? 'general');
if (!array_key_exists($selectedType, $config['bill_types'])) $selectedType = 'general';
$billNumber = 'BILL-' . strtoupper(date('Ymd')) . '-' . substr(strtoupper(bin2hex(random_bytes(3))), 0, 6);
?>
<link href="https://fonts.googleapis.com/css2?family=VT323&display=swap" rel="stylesheet">

<a href="/projects/billx" class="back-link"><i class="fas fa-arrow-left"></i> Dashboard</a>

<div style="margin-bottom:20px;">
    <h2 style="font-size:1.6rem;font-weight:700;background:linear-gradient(135deg,#f59e0b,#00f0ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
        <i class="fas fa-file-invoice" style="-webkit-text-fill-color:#f59e0b;"></i> Generate Bill
    </h2>
    <p style="color:var(--text-secondary);margin-top:4px;">Fill in the details and see a live preview of your bill</p>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div id="generateLayout" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;height:calc(100vh - 90px);">

    <!-- ====== LEFT PANEL: Scrollable Form ====== -->
    <div id="leftFormPanel" style="overflow-y:auto;height:100%;padding-right:4px;">
    <form method="POST" action="/projects/billx/generate" id="billForm">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    <div style="display:flex;flex-direction:column;gap:10px;">

            <!-- Bill Type & Number -->
            <div class="card">
                <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:12px;color:var(--amber);">
                    <i class="fas fa-tag"></i> Bill Details
                </h4>
                <div class="grid grid-2" style="gap:12px;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Bill Type</label>
                        <select name="bill_type" id="bill_type" class="form-select">
                            <?php foreach ($config['bill_types'] as $key => $label): ?>
                            <option value="<?= htmlspecialchars($key) ?>" <?= $key === $selectedType ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Bill Number</label>
                        <input type="text" name="bill_number" id="bill_number" class="form-input"
                               value="<?= htmlspecialchars($billNumber) ?>" required>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Bill Date</label>
                        <input type="date" name="bill_date" id="bill_date" class="form-input"
                               value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Currency</label>
                        <select name="currency" id="currency" class="form-select">
                            <option value="INR">INR ₹</option>
                            <option value="USD">USD $</option>
                            <option value="EUR">EUR €</option>
                            <option value="GBP">GBP £</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin:0;grid-column:span 2;">
                        <label class="form-label">Template Style</label>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:4px;" id="tplStyleGroup">
                            <label class="tpl-radio-label"><input type="radio" name="td_template_style" value="1" checked onchange="updatePreview()"> Style 1</label>
                            <label class="tpl-radio-label"><input type="radio" name="td_template_style" value="2" onchange="updatePreview()"> Style 2</label>
                            <label class="tpl-radio-label" id="tplStyle3Label"><input type="radio" name="td_template_style" value="3" onchange="updatePreview()"> Style 3 &mdash; Thermal</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:12px;color:var(--amber);">
                    <i class="fas fa-users"></i> Parties
                </h4>
                <div class="grid grid-2" style="gap:12px;">
                    <div>
                        <p id="fromLabel" style="font-size:0.78rem;font-weight:600;color:var(--text-secondary);margin-bottom:8px;text-transform:uppercase;letter-spacing:0.05em;">From (Issuer)</p>
                        <div class="form-group" style="margin-bottom:8px;">
                            <label class="form-label">Name *</label>
                            <input type="text" name="from_name" id="from_name" class="form-input" placeholder="Your name / company" required>
                        </div>
                        <div class="form-group" style="margin-bottom:8px;">
                            <label class="form-label">Address</label>
                            <textarea name="from_address" id="from_address" class="form-textarea" rows="2" placeholder="Address"></textarea>
                        </div>
                        <div class="form-group" style="margin-bottom:8px;">
                            <label class="form-label">Phone</label>
                            <input type="text" name="from_phone" id="from_phone" class="form-input" placeholder="+91 XXXXX XXXXX">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Email</label>
                            <input type="email" name="from_email" id="from_email" class="form-input" placeholder="email@example.com">
                        </div>
                    </div>
                    <div>
                        <p id="toLabel" style="font-size:0.78rem;font-weight:600;color:var(--text-secondary);margin-bottom:8px;text-transform:uppercase;letter-spacing:0.05em;">To (Recipient)</p>
                        <div class="form-group" style="margin-bottom:8px;">
                            <label class="form-label">Name *</label>
                            <input type="text" name="to_name" id="to_name" class="form-input" placeholder="Recipient name" required>
                        </div>
                        <div class="form-group" style="margin-bottom:8px;">
                            <label class="form-label">Address</label>
                            <textarea name="to_address" id="to_address" class="form-textarea" rows="2" placeholder="Address"></textarea>
                        </div>
                        <div class="form-group" style="margin-bottom:8px;">
                            <label class="form-label">Phone</label>
                            <input type="text" name="to_phone" id="to_phone" class="form-input" placeholder="+91 XXXXX XXXXX">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Email</label>
                            <input type="email" name="to_email" id="to_email" class="form-input" placeholder="email@example.com">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="card">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                    <h4 style="font-size:0.9rem;font-weight:600;color:var(--amber);">
                        <i class="fas fa-list-ul"></i> Items
                    </h4>
                    <button type="button" onclick="addItem()" class="btn btn-secondary btn-sm">
                        <i class="fas fa-plus"></i> Add Row
                    </button>
                </div>
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:0.82rem;" id="itemsTable">
                        <thead>
                            <tr style="border-bottom:1px solid var(--border-color);">
                                <th style="text-align:left;padding:6px 8px;color:var(--text-secondary);font-weight:600;">Description</th>
                                <th style="text-align:right;padding:6px 8px;color:var(--text-secondary);font-weight:600;width:70px;">Qty</th>
                                <th style="text-align:right;padding:6px 8px;color:var(--text-secondary);font-weight:600;width:100px;">Rate</th>
                                <th style="text-align:right;padding:6px 8px;color:var(--text-secondary);font-weight:600;width:100px;">Amount</th>
                                <th style="width:32px;"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <!-- Populated by JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Totals & Notes -->
            <div class="card">
                <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:12px;color:var(--amber);">
                    <i class="fas fa-calculator"></i> Totals & Notes
                </h4>
                <div class="grid grid-2" style="gap:12px;margin-bottom:12px;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Tax (%)</label>
                        <input type="number" name="tax_percent" id="tax_percent" class="form-input"
                               value="0" min="0" max="100" step="0.01">
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Discount (amount)</label>
                        <input type="number" name="discount_amount" id="discount_amount" class="form-input"
                               value="0" min="0" step="0.01">
                    </div>
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Notes / Remarks</label>
                    <textarea name="notes" id="notes" class="form-textarea" rows="2"
                              placeholder="Payment terms, remark, thank you note, etc."></textarea>
                </div>
            </div>

            <!-- Additional Details (type-specific) -->
            <div class="card" id="extraFieldsCard">
                <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:12px;color:var(--amber);">
                    <i class="fas fa-receipt"></i> <span id="extraFieldsLabel">Additional Details</span>
                </h4>
                <!-- Thermal: restaurant, recharge, mart, newspaper -->
                <fieldset id="fields_thermal" style="border:none;padding:0;margin:0;">
                    <div class="grid grid-2" style="gap:12px;">
                        <div class="form-group" style="margin:0;"><label class="form-label">Table / Token #</label>
                            <input type="text" name="td_table_number" class="form-input" placeholder="e.g. 50" oninput="updatePreview()"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">Payment Mode</label>
                            <select name="td_payment_mode" class="form-select" onchange="updatePreview()">
                                <option value="Cash">Cash</option><option value="Card">Card / Swipe</option>
                                <option value="UPI">UPI</option><option value="Online">Online</option>
                            </select></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">Bill Time</label>
                            <input type="time" name="td_bill_time" id="td_bill_time" class="form-input" oninput="updatePreview()"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">CGST %</label>
                            <input type="number" name="td_cgst_pct" class="form-input" value="0" min="0" max="50" step="0.01" oninput="updatePreview()"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">SGST %</label>
                            <input type="number" name="td_sgst_pct" class="form-input" value="0" min="0" max="50" step="0.01" oninput="updatePreview()"></div>
                    </div>
                </fieldset>
                <!-- Payslip: driver, helper -->
                <fieldset id="fields_payslip" style="border:none;padding:0;margin:0;">
                    <div class="grid grid-2" style="gap:12px;">
                        <div class="form-group" style="margin:0;"><label class="form-label">Vehicle Number</label>
                            <input type="text" name="td_vehicle_number" class="form-input" placeholder="e.g. DL01AB-1234" oninput="updatePreview()"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">Employer Name</label>
                            <input type="text" name="td_employer_name" class="form-input" placeholder="Company / Owner name" oninput="updatePreview()"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">Month of Salary</label>
                            <input type="text" name="td_salary_month" class="form-input" placeholder="e.g. March 2026" oninput="updatePreview()"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">Employee Designation</label>
                            <input type="text" name="td_designation" class="form-input" placeholder="e.g. Driver" oninput="updatePreview()"></div>
                    </div>
                </fieldset>
                <!-- Fuel -->
                <fieldset id="fields_fuel" style="border:none;padding:0;margin:0;">
                    <div class="grid grid-2" style="gap:12px;">
                        <div class="form-group" style="margin:0;"><label class="form-label">Vehicle Number</label>
                            <input type="text" name="td_vehicle_number" class="form-input" placeholder="e.g. DL01AB-1234" oninput="updatePreview()"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">Vehicle Type</label>
                            <select name="td_vehicle_type" class="form-select" onchange="updatePreview()">
                                <option value="Car">Car</option><option value="Bike">Bike / Two-Wheeler</option>
                                <option value="Truck">Truck</option><option value="Auto">Auto</option>
                            </select></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">Fuel Type</label>
                            <select name="td_fuel_type" class="form-select" onchange="updatePreview()">
                                <option value="Petrol">Petrol</option><option value="Diesel">Diesel</option>
                                <option value="CNG">CNG</option><option value="Electric">Electric</option>
                            </select></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">Payment Method</label>
                            <select name="td_payment_mode" class="form-select" onchange="updatePreview()">
                                <option value="Cash">Cash</option><option value="Card">Card</option>
                                <option value="UPI">UPI</option><option value="Prepaid">Prepaid</option>
                            </select></div>
                    </div>
                </fieldset>
                <!-- Cab -->
                <fieldset id="fields_cab" style="border:none;padding:0;margin:0;">
                    <div class="grid grid-2" style="gap:12px;">
                        <div class="form-group" style="margin:0;"><label class="form-label">Cab / Vehicle Number</label>
                            <input type="text" name="td_vehicle_number" class="form-input" placeholder="e.g. MH02AB-5678" oninput="updatePreview()"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">Driver Name</label>
                            <input type="text" name="td_driver_name" class="form-input" placeholder="Driver name" oninput="updatePreview()"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">Pickup Location</label>
                            <input type="text" name="td_pickup" class="form-input" placeholder="Pickup point" oninput="updatePreview()"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">Drop Location</label>
                            <input type="text" name="td_drop" class="form-input" placeholder="Drop point" oninput="updatePreview()"></div>
                    </div>
                </fieldset>
                <!-- Official: rent, lta -->
                <fieldset id="fields_official" style="border:none;padding:0;margin:0;">
                    <div class="grid grid-2" style="gap:12px;">
                        <div class="form-group" style="margin:0;"><label class="form-label">PAN Number</label>
                            <input type="text" name="td_pan_number" class="form-input" placeholder="Landlord / Company PAN" oninput="updatePreview()"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">Property / Office Info</label>
                            <input type="text" name="td_property_info" class="form-input" placeholder="2BHK, Building name, etc." oninput="updatePreview()"></div>
                    </div>
                </fieldset>
                <!-- Medical -->
                <fieldset id="fields_medical" style="border:none;padding:0;margin:0;">
                    <div class="grid grid-2" style="gap:12px;">
                        <div class="form-group" style="margin:0;"><label class="form-label">Consultant / Doctor</label>
                            <input type="text" name="td_doctor_name" class="form-input" placeholder="Dr. Name" oninput="updatePreview()"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">Patient ID / OPD No.</label>
                            <input type="text" name="td_patient_id" class="form-input" placeholder="e.g. OPD-2026-001" oninput="updatePreview()"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">Patient Issue / Diagnosis</label>
                            <input type="text" name="td_patient_issue" class="form-input" placeholder="Chief complaint" oninput="updatePreview()"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">Guardian Name</label>
                            <input type="text" name="td_guardian_name" class="form-input" placeholder="Guardian / relative" oninput="updatePreview()"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">Admit Date</label>
                            <input type="date" name="td_admit_date" class="form-input" oninput="updatePreview()"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">Room Category</label>
                            <select name="td_room_category" class="form-select" onchange="updatePreview()">
                                <option value="General">General Ward</option><option value="Single">Single Room</option>
                                <option value="Semi-Private">Semi-Private</option><option value="ICU">ICU</option>
                            </select></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">CGST %</label>
                            <input type="number" name="td_cgst_pct" class="form-input" value="0" min="0" max="50" step="0.01" oninput="updatePreview()"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">SGST %</label>
                            <input type="number" name="td_sgst_pct" class="form-input" value="0" min="0" max="50" step="0.01" oninput="updatePreview()"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">Patient Age</label>
                            <input type="text" name="td_patient_age" class="form-input" placeholder="e.g. 35 yrs" oninput="updatePreview()"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">Insurance Available</label>
                            <select name="td_insurance" class="form-select" onchange="updatePreview()">
                                <option value="Yes">Yes</option><option value="No">No</option>
                            </select></div>
                    </div>
                </fieldset>
                <!-- Hotel -->
                <fieldset id="fields_hotel" style="border:none;padding:0;margin:0;">
                    <div class="grid grid-2" style="gap:12px;">
                        <div class="form-group" style="margin:0;"><label class="form-label">Room Number / Type</label>
                            <input type="text" name="td_room_number" class="form-input" placeholder="e.g. 412 - Deluxe" oninput="updatePreview()"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">GSTIN</label>
                            <input type="text" name="td_gstin" class="form-input" placeholder="Hotel GSTIN" oninput="updatePreview()"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">Check-in Date</label>
                            <input type="date" name="td_checkin_date" class="form-input" oninput="updatePreview()"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">Check-out Date</label>
                            <input type="date" name="td_checkout_date" class="form-input" oninput="updatePreview()"></div>
                    </div>
                </fieldset>
                <!-- Gym -->
                <fieldset id="fields_gym" style="border:none;padding:0;margin:0;">
                    <div class="grid grid-2" style="gap:12px;">
                        <div class="form-group" style="margin:0;"><label class="form-label">Member ID</label>
                            <input type="text" name="td_member_id" class="form-input" placeholder="e.g. MBR-2026-045" oninput="updatePreview()"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">Plan / Package</label>
                            <input type="text" name="td_plan_name" class="form-input" placeholder="e.g. 3-Month Premium" oninput="updatePreview()"></div>
                    </div>
                </fieldset>
                <!-- Invoice: book, internet, ecom, general, recharge, newspaper -->
                <fieldset id="fields_invoice" style="border:none;padding:0;margin:0;">
                    <div class="grid grid-2" style="gap:12px;">
                        <div class="form-group" style="margin:0;"><label class="form-label">Seller GSTIN</label>
                            <input type="text" name="td_gstin" class="form-input" placeholder="e.g. 27ABCDE1234F1Z5" oninput="updatePreview()"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">HSN / SAC Code</label>
                            <input type="text" name="td_hsn_code" class="form-input" placeholder="e.g. 9983" oninput="updatePreview()"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">CGST %</label>
                            <input type="number" name="td_cgst_pct" class="form-input" value="0" min="0" max="50" step="0.01" oninput="updatePreview()"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">SGST %</label>
                            <input type="number" name="td_sgst_pct" class="form-input" value="0" min="0" max="50" step="0.01" oninput="updatePreview()"></div>
                        <div class="form-group" style="margin:0;grid-column:span 2;"><label class="form-label">Place of Supply</label>
                            <input type="text" name="td_place_of_supply" class="form-input" placeholder="e.g. Maharashtra" oninput="updatePreview()"></div>
                    </div>
                </fieldset>
            </div>

            <!-- Policy Agreement -->
            <?php $requirePolicy = !empty($config['admin_settings']['require_policy_agree'] ?? 1); ?>
            <div class="card" <?= !$requirePolicy ? 'style="display:none;"' : '' ?>>
                <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;">
                    <input type="checkbox" name="policy_agree" id="policy_agree"
                           <?= $requirePolicy ? 'required' : '' ?>
                           style="margin-top:3px;width:16px;height:16px;accent-color:var(--amber);flex-shrink:0;">
                    <span style="font-size:0.85rem;color:var(--text-secondary);line-height:1.5;">
                        <strong style="color:var(--text-primary);">Policy Agreement</strong><br>
                        I am authorized to use the logo / brand name for generating this bill. I understand that BillX is a bill generator tool and takes no responsibility for misuse.
                    </span>
                </label>
            </div>

            <!-- Submit Actions -->
            <input type="hidden" name="save_action" id="save_action" value="save">
            <div class="form-actions" style="justify-content:flex-start;gap:8px;flex-wrap:wrap;">
                <button type="button" class="btn btn-secondary form-btn-compact"
                        onclick="submitBill('save')">
                    <i class="fas fa-save"></i> Save
                </button>
                <button type="button" class="btn btn-success form-btn-compact"
                        onclick="submitBill('download')">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </button>
            </div>
        </div>
    </form>
    </div><!-- /leftFormPanel -->

        <!-- ====== RIGHT PANEL: Live Preview ====== -->
        <div id="rightPreviewPanel" style="height:100%;display:flex;flex-direction:column;">
            <div class="card" style="padding:0;display:flex;flex-direction:column;flex:1;min-height:0;overflow:hidden;">
                <div style="background:var(--bg-secondary);padding:10px 14px;border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;flex-shrink:0;">
                    <span style="font-size:0.8rem;font-weight:600;color:var(--text-secondary);">
                        <i class="fas fa-eye"></i> Live Preview
                    </span>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <button type="button" id="crambleBtn" onclick="toggleCrambled()"
                                style="font-size:0.7rem;padding:3px 9px;border-radius:12px;border:1px solid var(--border-color);background:transparent;color:var(--text-secondary);cursor:pointer;display:flex;align-items:center;gap:4px;">
                            <i class="fas fa-scroll"></i> Crumpled
                        </button>
                        <span id="previewTypeBadge" style="font-size:0.68rem;padding:3px 9px;border-radius:12px;background:#f59e0b;color:white;font-weight:600;"></span>
                    </div>
                </div>
                <div style="padding:12px;background:#f0f0f0;flex:1;overflow-y:auto;min-height:0;" id="billPreviewWrapper">
                    <div id="billPreview" style="background:white;max-width:520px;margin:0 auto;font-family:'Poppins',sans-serif;font-size:13px;color:#333;box-shadow:0 2px 16px rgba(0,0,0,0.12);border-radius:4px;overflow:hidden;">
                        <!-- Preview rendered by JS -->
                    </div>
                </div>
            </div>
        </div>

</div><!-- /generateLayout -->

<style>
/* Generate page layout: two-pane scrolling, no billx-main overflow override needed */
@media (min-width: 901px) {
    .billx-main { overflow: hidden !important; }
}
@media (max-width: 900px) {
    #generateLayout { height: auto !important; grid-template-columns: 1fr !important; }
    #leftFormPanel  { overflow-y: visible !important; height: auto !important; max-height: none !important; }
    #rightPreviewPanel { height: 520px !important; min-height: 400px; }
}
@media (max-width: 480px) {
    #generateLayout { gap: 10px !important; }
    #rightPreviewPanel { height: 420px !important; }
}
/* Compact form overrides for generate page */
#billForm .form-input,
#billForm .form-select,
#billForm .form-textarea,
#billForm .form-control {
    padding: 0.4rem 0.65rem;
    font-size: 0.8rem;
}
#billForm .form-label {
    font-size: 0.75rem;
    margin-bottom: 3px;
}
#billForm .form-group {
    margin-bottom: 8px;
}
#billForm .card {
    padding: 10px 12px;
}
#billForm .card h4 {
    font-size: 0.82rem !important;
    margin-bottom: 8px !important;
}
/* Green download button */
.btn-success {
    background: linear-gradient(135deg, #10b981, #059669); color: white;
    display: inline-flex; align-items: center; justify-content: center;
    gap: 0.375rem; border: none; border-radius: 0.5rem;
    font-family: inherit; font-weight: 600; cursor: pointer;
    transition: all 0.3s ease; text-decoration: none; white-space: nowrap;
}
.btn-success:hover:not(:disabled) { box-shadow: 0 0.375rem 1.5rem rgba(16,185,129,0.45); transform: translateY(-0.125rem); }
/* Compact form action buttons */
.form-btn-compact {
    font-size: 0.875rem !important;
    padding: 8px 16px !important;
}
.item-row td { padding: 4px 6px; }
.item-row input[type="text"],
.item-row input[type="number"] {
    width: 100%;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 4px;
    color: var(--text-primary);
    padding: 4px 7px;
    font-family: inherit;
    font-size: 0.79rem;
}
.item-row input:focus { outline: none; border-color: var(--amber); }
.remove-item-btn {
    background: none; border: none; color: #ff6b6b; cursor: pointer;
    font-size: 0.9rem; padding: 4px; line-height: 1;
}
.remove-item-btn:hover { color: #ff4757; }
/* ── Crumpled paper effect ─────────────────────────────── */
#billPreviewWrapper.crambled {
    background-color: #9e9b96 !important;
    background-image:
        repeating-linear-gradient(
            157deg,
            transparent 0, transparent 88px,
            rgba(0,0,0,.065) 88px, rgba(255,255,255,.55) 89px,
            rgba(0,0,0,.025) 90px, transparent 91px
        ),
        repeating-linear-gradient(
            -53deg,
            transparent 0, transparent 110px,
            rgba(0,0,0,.05) 110px, rgba(255,255,255,.45) 111px,
            rgba(0,0,0,.02) 112px, transparent 113px
        ),
        repeating-linear-gradient(
            73deg,
            transparent 0, transparent 148px,
            rgba(0,0,0,.04) 148px, rgba(255,255,255,.38) 149px,
            transparent 150px
        ),
        repeating-linear-gradient(
            180deg,
            rgba(255,255,255,.018) 0, rgba(255,255,255,.018) 1px,
            transparent 1px, transparent 3px
        ) !important;
}
#billPreviewWrapper.crambled #billPreview {
    position: relative;
    background: #fdfcfa !important;
    box-shadow:
        -6px 4px 22px rgba(0,0,0,.22),
        6px -3px 16px rgba(0,0,0,.15),
        0 12px 40px rgba(0,0,0,.28);
    transform: rotate(0.45deg) skew(-0.12deg, 0.1deg);
    filter: brightness(0.99) contrast(1.02);
}
#billPreviewWrapper.crambled #billPreview::before {
    content: '';
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 9999;
    background:
        linear-gradient(148deg, transparent 37%, rgba(0,0,0,.042) 37.3%, rgba(255,255,255,.82) 37.7%, rgba(0,0,0,.018) 38%, transparent 38.4%),
        linear-gradient(-46deg, transparent 44%, rgba(0,0,0,.035) 44.3%, rgba(255,255,255,.7) 44.7%, rgba(0,0,0,.015) 45%, transparent 45.4%),
        linear-gradient(71deg, transparent 23%, rgba(0,0,0,.028) 23.3%, rgba(255,255,255,.6) 23.7%, transparent 24.1%),
        radial-gradient(ellipse 35% 30% at 0% 0%, rgba(0,0,0,.08) 0%, transparent 100%),
        radial-gradient(ellipse 30% 25% at 100% 100%, rgba(0,0,0,.06) 0%, transparent 100%);
    mix-blend-mode: multiply;
}
#crambleBtn.active {
    background: var(--amber) !important;
    color: #fff !important;
    border-color: var(--amber) !important;
}
/* fieldset reset */
#extraFieldsCard fieldset { border:none;padding:0;margin:0; }
/* Template style radio pills */
.tpl-radio-label {
    display:flex;align-items:center;gap:5px;cursor:pointer;
    padding:4px 10px;border-radius:20px;border:1px solid var(--border-color);
    font-size:0.78rem;color:var(--text-secondary);transition:border-color .15s,color .15s;
}
.tpl-radio-label:has(input:checked) {
    border-color:var(--amber);color:var(--amber);font-weight:600;
}
.tpl-radio-label input[type="radio"] { accent-color:var(--amber); }
</style>

<script>
// ─── Helpers ──────────────────────────────────────────────────────────────────
function escHtml(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
const CURRENCY_SYM={INR:'₹',USD:'$',EUR:'€',GBP:'£'};
function fmtDate(d){if(!d)return '';const p=d.split('-');const m=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];return p[2]+' '+m[parseInt(p[1])-1]+' '+p[0];}
function nowTime(){return new Date().toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit'});}

// ─── Bill group mapping ───────────────────────────────────────────────────────
const BILL_GROUPS={thermal:['restaurant','mart','stationary'],payslip:['driver','helper'],fuel:['fuel'],cab:['cab'],official:['rent','lta'],medical:['medical'],hotel:['hotel'],gym:['gym'],invoice:['book','internet','ecom','general','recharge','newspaper']};
function getGroup(type){for(const[g,a]of Object.entries(BILL_GROUPS))if(a.includes(type))return g;return 'invoice';}
const TYPE_LABELS={fuel:'Fuel Bill',driver:'Driver Salary',helper:'Daily Helper Bill',rent:'Rent Receipt',book:'Book Invoice',internet:'Internet Invoice',restaurant:'Restaurant Bill',lta:'LTA Receipt',ecom:'E-Com Invoice',general:'General Bill',recharge:'Recharge Receipt',medical:'Medical Bill',stationary:'Stationary Bill',cab:'Cab & Travel Bill',mart:'Mart Bill',gym:'Gym Bill',hotel:'Hotel Bill',newspaper:'Newspaper Bill'};
const TYPE_COLORS={fuel:'#e65000',driver:'#1565c0',helper:'#546e7a',rent:'#6a1b9a',book:'#5d4037',internet:'#0277bd',restaurant:'#c62828',lta:'#2e7d32',ecom:'#1565c0',general:'#37474f',recharge:'#00838f',medical:'#0077b6',stationary:'#bf360c',cab:'#e65100',mart:'#1b5e20',gym:'#212121',hotel:'#7d5a00',newspaper:'#1a1a1a'};

// ─── Category-specific party labels ──────────────────────────────────────────
const FROM_LABELS={restaurant:'Restaurant / Diner',recharge:'Service Provider',mart:'Store / Mart',newspaper:'Publisher / Agent',driver:'Employer / Company',helper:'Employer / Company',fuel:'Fuel Station',cab:'Cab Company',rent:'Landlord / Owner',lta:'Employer / Company',medical:'Hospital / Clinic',hotel:'Hotel / Resort',gym:'Fitness Center / Gym',book:'Publisher / Seller',internet:'ISP / Provider',ecom:'Online Store',general:'Seller / Issuer',stationary:'Stationery Shop'};
const TO_LABELS={restaurant:'Customer',recharge:'Customer',mart:'Customer',newspaper:'Subscriber',driver:'Employee / Driver',helper:'Employee / Helper',fuel:'Vehicle Owner',cab:'Passenger',rent:'Tenant',lta:'Employee',medical:'Patient',hotel:'Guest',gym:'Member',book:'Buyer / Student',internet:'Subscriber',ecom:'Buyer',general:'Buyer / Recipient',stationary:'Customer'};
function updateFormLabels(type){
    const fl=document.getElementById('fromLabel');
    const tl=document.getElementById('toLabel');
    if(fl)fl.textContent=FROM_LABELS[type]||'From (Issuer)';
    if(tl)tl.textContent=TO_LABELS[type]||'To (Recipient)';
}

// ─── Extra-fields per group ───────────────────────────────────────────────────
const GROUP_FIELDSETS={thermal:'fields_thermal',payslip:'fields_payslip',fuel:'fields_fuel',cab:'fields_cab',official:'fields_official',medical:'fields_medical',hotel:'fields_hotel',gym:'fields_gym',invoice:'fields_invoice'};
const EXTRA_LABELS={thermal:'POS / Store Details (Restaurant, Mart, Stationary)',payslip:'Employee Details',fuel:'Fuel & Vehicle Details',cab:'Cab & Driver Details',official:'Property / Official Details',medical:'Patient & Hospital Details',hotel:'Hotel & Room Details',gym:'Gym & Membership Details',invoice:'GST & Invoice Details'};

function syncExtraFields(type){
    const group=getGroup(type);
    Object.entries(GROUP_FIELDSETS).forEach(([g,id])=>{
        const fs=document.getElementById(id);if(!fs)return;
        const active=g===group;fs.style.display=active?'':'none';
        // Keep inputs in inactive sections visually hidden but not form-disabled
        // so events fire correctly for the active group
        fs.querySelectorAll('input,select,textarea').forEach(el=>{el.disabled=!active;});
    });
    const lbl=document.getElementById('extraFieldsLabel');
    if(lbl)lbl.textContent=EXTRA_LABELS[group]||'Additional Details';
    updateFormLabels(type);
    // Show Style 3 (Thermal) option only for thermal bill types
    const tpl3Label=document.getElementById('tplStyle3Label');
    if(tpl3Label){
        if(group==='thermal'){tpl3Label.style.display='';}
        else{
            tpl3Label.style.display='none';
            // If style 3 was selected and type is now non-thermal, switch to style 1
            const s3=document.querySelector('input[name="td_template_style"][value="3"]');
            if(s3&&s3.checked){const s1=document.querySelector('input[name="td_template_style"][value="1"]');if(s1)s1.checked=true;}
        }
    }
}
function getExtraFields(){
    const type=document.getElementById('bill_type').value;
    const group=getGroup(type);
    const fs=document.getElementById(GROUP_FIELDSETS[group]);
    const td={};
    if(fs)fs.querySelectorAll('input,select,textarea').forEach(el=>{if(el.name&&el.name.startsWith('td_'))td[el.name.slice(3)]=el.value;});
    const tplStyleEl=document.querySelector('input[name="td_template_style"]:checked');
    if(tplStyleEl)td.template_style=tplStyleEl.value;
    return td;
}

// ─── Items management ─────────────────────────────────────────────────────────
function addItem(desc='',qty=1,rate=0){
    const tbody=document.getElementById('itemsBody');
    const amount=(qty*rate).toFixed(2);
    const tr=document.createElement('tr');tr.className='item-row';
    tr.innerHTML=`<td><input type="text" name="item_description[]" placeholder="Description" value="${escHtml(desc)}" oninput="updatePreview()"></td><td><input type="number" name="item_qty[]" value="${qty}" min="0" step="any" style="text-align:right;" oninput="calcRow(this)"></td><td><input type="number" name="item_rate[]" value="${rate}" min="0" step="any" style="text-align:right;" oninput="calcRow(this)"></td><td style="text-align:right;font-weight:600;padding:4px 8px;" class="row-amount">${amount}</td><td><button type="button" class="remove-item-btn" onclick="removeItem(this)" title="Remove"><i class="fas fa-times"></i></button></td>`;
    tbody.appendChild(tr);updatePreview();
}
function removeItem(btn){btn.closest('tr').remove();updatePreview();}
function calcRow(input){const tr=input.closest('tr');const qty=parseFloat(tr.querySelector('[name="item_qty[]"]').value)||0;const rate=parseFloat(tr.querySelector('[name="item_rate[]"]').value)||0;tr.querySelector('.row-amount').textContent=(qty*rate).toFixed(2);updatePreview();}
function getItems(){return Array.from(document.querySelectorAll('#itemsBody .item-row')).map(tr=>({description:tr.querySelector('[name="item_description[]"]').value,qty:parseFloat(tr.querySelector('[name="item_qty[]"]').value)||0,rate:parseFloat(tr.querySelector('[name="item_rate[]"]').value)||0,amount:parseFloat(tr.querySelector('.row-amount').textContent)||0}));}

function collectData(){
    const type=document.getElementById('bill_type').value;
    const currency=document.getElementById('currency').value;
    const sym=CURRENCY_SYM[currency]||currency+' ';
    const items=getItems();
    const subtotal=items.reduce((s,it)=>s+it.amount,0);
    const taxPct=parseFloat(document.getElementById('tax_percent').value)||0;
    const discount=parseFloat(document.getElementById('discount_amount').value)||0;
    const td=getExtraFields();
    const cgstPct=parseFloat(td.cgst_pct)||0;const sgstPct=parseFloat(td.sgst_pct)||0;
    const cgstAmtCalc=subtotal*cgstPct/100;const sgstAmtCalc=subtotal*sgstPct/100;
    const taxAmt=subtotal*taxPct/100;
    const total=subtotal+taxAmt+cgstAmtCalc+sgstAmtCalc-discount;
    return{type,sym,currency,group:getGroup(type),billNo:document.getElementById('bill_number').value||'-',billDate:document.getElementById('bill_date').value||'',fromName:document.getElementById('from_name').value||'',fromAddr:document.getElementById('from_address').value||'',fromPhone:document.getElementById('from_phone').value||'',fromEmail:document.getElementById('from_email').value||'',toName:document.getElementById('to_name').value||'',toAddr:document.getElementById('to_address').value||'',toPhone:document.getElementById('to_phone').value||'',toEmail:document.getElementById('to_email').value||'',taxPct,discount,taxAmt,subtotal,total,notes:document.getElementById('notes').value||'',items,td};
}

// ─── RENDERER 1: Thermal / POS Receipt ───────────────────────────────────────
// (restaurant, mart, stationary)
function renderThermal(d){
    const style = d.td.template_style || '1';
    if (style === '2') {
    // Style 2: Clean restaurant bill (sans-serif, colored header)
    const cgst=parseFloat(d.td.cgst_pct)||0; const sgst=parseFloat(d.td.sgst_pct)||0;
    const cgstAmt=d.subtotal*cgst/100; const sgstAmt=d.subtotal*sgst/100;
    const mode=d.td.payment_mode||''; const tableNo=d.td.table_number||'';
    const c=TYPE_COLORS[d.type]||'#c62828';
    const rows=d.items.map((it,i)=>`<tr style="background:${i%2===0?'#fafafa':'#fff'};"><td style="padding:5px 8px;font-size:12px;">${escHtml(it.description||'-')}</td><td style="padding:5px 8px;text-align:center;font-size:11px;">${it.qty}</td><td style="padding:5px 8px;text-align:right;font-size:12px;">${d.sym}${it.rate.toFixed(2)}</td><td style="padding:5px 8px;text-align:right;font-weight:700;font-size:12px;">${d.sym}${it.amount.toFixed(2)}</td></tr>`).join('');
    return `<div style="font-family:Arial,sans-serif;background:#fff;width:80mm;max-width:80mm;margin:0 auto;border:1px solid #ddd;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,.12);">
<div style="background:${c};color:#fff;padding:12px 16px;text-align:center;">
<div style="font-size:18px;font-weight:700;">${escHtml(d.fromName)}</div>
${d.fromAddr?`<div style="font-size:10px;opacity:.85;margin-top:2px;">${escHtml(d.fromAddr).replace(/\n/g,' | ')}</div>`:''}
${d.fromPhone?`<div style="font-size:10px;opacity:.85;">Ph: ${escHtml(d.fromPhone)}</div>`:''}
${d.fromEmail?`<div style="font-size:10px;opacity:.85;">${escHtml(d.fromEmail)}</div>`:''}
</div>
<div style="padding:8px 12px;background:#f5f5f5;border-bottom:1px solid #ddd;display:flex;justify-content:space-between;font-size:11px;">
<span>Bill No: <b>${escHtml(d.billNo)}</b></span>${tableNo?`<span>Table: <b>#${escHtml(tableNo)}</b></span>`:''}
<span>${fmtDate(d.billDate)}</span>
</div>
<div style="padding:6px 12px;font-size:11px;border-bottom:1px solid #eee;">Customer: <b>${escHtml(d.toName)}</b>${d.toPhone?` | Ph: ${escHtml(d.toPhone)}`:''}${mode?` | Mode: <b>${escHtml(mode)}</b>`:''}</div>
<table style="width:100%;border-collapse:collapse;"><thead><tr style="background:${c};color:#fff;"><th style="padding:5px 8px;text-align:left;font-size:11px;">Item</th><th style="padding:5px 8px;text-align:center;font-size:11px;">Qty</th><th style="padding:5px 8px;text-align:right;font-size:11px;">Rate</th><th style="padding:5px 8px;text-align:right;font-size:11px;">Amt</th></tr></thead>
<tbody>${rows||'<tr><td colspan="4" style="padding:8px;text-align:center;color:#aaa;">No items</td></tr>'}</tbody></table>
<div style="padding:8px 12px;background:#f9f9f9;border-top:1px solid #ddd;font-size:11px;">
<div style="display:flex;justify-content:space-between;padding:2px 0;"><span>Sub-Total</span><span>${d.sym}${d.subtotal.toFixed(2)}</span></div>
${cgst>0?`<div style="display:flex;justify-content:space-between;padding:2px 0;"><span>CGST ${cgst}%</span><span>${d.sym}${cgstAmt.toFixed(2)}</span></div>`:''}
${sgst>0?`<div style="display:flex;justify-content:space-between;padding:2px 0;"><span>SGST ${sgst}%</span><span>${d.sym}${sgstAmt.toFixed(2)}</span></div>`:''}
${d.discount>0?`<div style="display:flex;justify-content:space-between;padding:2px 0;"><span>Discount</span><span>-${d.sym}${d.discount.toFixed(2)}</span></div>`:''}
<div style="display:flex;justify-content:space-between;font-size:14px;font-weight:900;border-top:2px solid ${c};margin-top:4px;padding-top:4px;color:${c};"><span>TOTAL</span><span>${d.sym}${d.total.toFixed(2)}</span></div>
</div>
${d.notes?`<div style="padding:6px 12px;font-size:10px;color:#555;border-top:1px solid #eee;">${escHtml(d.notes)}</div>`:''}
<div style="padding:6px;text-align:center;font-size:9px;color:#999;background:#f0f0f0;">Thank you! Visit again | Time: ${nowTime()}</div>
</div>`;
    }
    if (style === '3') {
    // Style 3: Real Thermal POS Printer Receipt (80mm roll, VT323 font)
    const cgst=parseFloat(d.td.cgst_pct)||0; const sgst=parseFloat(d.td.sgst_pct)||0;
    const cgstAmt=d.subtotal*cgst/100; const sgstAmt=d.subtotal*sgst/100;
    const mode=d.td.payment_mode||''; const tableNo=d.td.table_number||'';
    const billTime=d.td.bill_time||nowTime();
    const itemRows=d.items.map(it=>`<div style="padding:3px 0;border-bottom:1px dotted #ccc;"><div style="font-size:15px;">${escHtml(it.description||'-')}</div><div style="display:flex;justify-content:space-between;font-size:13px;color:#555;"><span>${it.qty}&times;${d.sym}${it.rate.toFixed(2)}</span><span style="font-weight:700;color:#111;">${d.sym}${it.amount.toFixed(2)}</span></div></div>`).join('');
    return `<div style="font-family:'VT323','Courier New',monospace;background:#fff;width:80mm;max-width:80mm;margin:0 auto;color:#111;box-shadow:0 3px 12px rgba(0,0,0,.18);letter-spacing:.4px;">
<div style="padding:12px 14px 10px;">
<div style="text-align:center;padding-bottom:4px;margin-bottom:4px;">
<div style="font-size:22px;font-weight:700;letter-spacing:4px;">WELCOME!!!</div>
<div style="font-size:14px;letter-spacing:2px;">Original Receipt</div>
<div style="border-top:1px dashed #555;margin:5px 0;"></div>
<div style="font-size:20px;font-weight:700;letter-spacing:2px;line-height:1.1;">${escHtml(d.fromName.toUpperCase())}</div>
${d.fromAddr?`<div style="font-size:12px;color:#444;margin-top:2px;">${escHtml(d.fromAddr).replace(/\n/g,' | ')}</div>`:''}
${d.fromPhone?`<div style="font-size:12px;color:#444;">Ph: ${escHtml(d.fromPhone)}</div>`:''}
${d.fromEmail?`<div style="font-size:12px;color:#444;">${escHtml(d.fromEmail)}</div>`:''}
<div style="border-top:1px dashed #555;margin:5px 0;"></div>
</div>
<div style="font-size:14px;border-bottom:1px dashed #555;padding-bottom:5px;margin-bottom:5px;">
<div style="display:flex;justify-content:space-between;"><span>Bill#: <b>${escHtml(d.billNo)}</b></span><span>${fmtDate(d.billDate)}</span></div>
<div style="display:flex;justify-content:space-between;"><span>Cust: <b>${escHtml(d.toName)}</b>${d.toPhone?` | ${escHtml(d.toPhone)}`:''}</span><span>${billTime}</span></div>
${tableNo?`<div>Table: <b>#${escHtml(tableNo)}</b>${mode?' | Pay: <b>'+escHtml(mode)+'</b>':''}</div>`:''}
</div>
<div style="border-bottom:1px dashed #555;padding-bottom:5px;margin-bottom:4px;">
<div style="display:flex;justify-content:space-between;font-size:13px;font-weight:700;padding:2px 0;border-bottom:1px solid #ccc;"><span>ITEM</span><span>QTY&times;RATE</span><span>AMT</span></div>
${itemRows||'<div style="font-size:14px;color:#aaa;padding:4px 0;text-align:center;">No items added</div>'}
</div>
<div style="font-size:14px;padding:2px 0;">
<div style="display:flex;justify-content:space-between;padding:1px 0;"><span>Sub-Total:</span><span>${d.sym}${d.subtotal.toFixed(2)}</span></div>
${cgst>0?`<div style="display:flex;justify-content:space-between;padding:1px 0;"><span>CGST ${cgst}%:</span><span>${d.sym}${cgstAmt.toFixed(2)}</span></div>`:''}
${sgst>0?`<div style="display:flex;justify-content:space-between;padding:1px 0;"><span>SGST ${sgst}%:</span><span>${d.sym}${sgstAmt.toFixed(2)}</span></div>`:''}
${d.taxPct>0&&cgst===0&&sgst===0?`<div style="display:flex;justify-content:space-between;padding:1px 0;"><span>Tax ${d.taxPct}%:</span><span>${d.sym}${d.taxAmt.toFixed(2)}</span></div>`:''}
${d.discount>0?`<div style="display:flex;justify-content:space-between;padding:1px 0;"><span>Discount:</span><span>-${d.sym}${d.discount.toFixed(2)}</span></div>`:''}
</div>
<div style="border-top:2px solid #111;padding:4px 0;margin-top:2px;">
<div style="display:flex;justify-content:space-between;font-size:20px;font-weight:900;"><span>** TOTAL</span><span>${d.sym}${d.total.toFixed(2)}</span></div>
${mode&&!tableNo?`<div style="font-size:14px;color:#555;">Payment: ${escHtml(mode)}</div>`:''}
</div>
${d.notes?`<div style="border-top:1px dashed #555;padding-top:4px;margin-top:3px;font-size:13px;color:#555;text-align:center;">${escHtml(d.notes)}</div>`:''}
<div style="border-top:1px dashed #555;padding-top:6px;margin-top:5px;text-align:center;font-size:15px;">
<div style="font-weight:700;letter-spacing:1px;">THANK YOU! VISIT AGAIN!</div>
<div style="font-size:13px;color:#555;">** SAVE PAPER ~ SAVE NATURE **</div>
<div style="font-size:12px;color:#888;margin-top:3px;">Powered by BillX</div>
</div>
</div>
</div>`;
    }
    const c=TYPE_COLORS[d.type]||'#333';
    const dash='<div style="border-top:1px dashed #888;margin:6px 0;"></div>';
    const cgst=parseFloat(d.td.cgst_pct)||0;const sgst=parseFloat(d.td.sgst_pct)||0;
    const cgstAmt=d.subtotal*cgst/100;const sgstAmt=d.subtotal*sgst/100;
    const mode=d.td.payment_mode||'';const tableNo=d.td.table_number||'';
    const items=d.items.map(it=>`<tr><td style="padding:2px 3px;font-size:11px;">${escHtml(it.description||'-')}</td><td style="padding:2px 3px;text-align:right;font-size:11px;">${d.sym}${it.rate.toFixed(2)}</td><td style="padding:2px 3px;text-align:center;font-size:11px;">${it.qty}</td><td style="padding:2px 3px;text-align:right;font-weight:700;font-size:11px;">${d.sym}${it.amount.toFixed(2)}</td></tr>`).join('');
    return`<div style="font-family:'Courier New',monospace;background:#fff;width:80mm;max-width:80mm;margin:0 auto;padding:14px 18px;font-size:11px;color:#111;border:1px solid #ccc;box-shadow:1px 2px 8px rgba(0,0,0,.15);">
${dash}<div style="text-align:center;letter-spacing:6px;font-size:11px;font-weight:700;margin:2px 0;">RECEIPT</div>${dash}
<div style="display:flex;justify-content:space-between;margin-bottom:2px;"><span>Name: <b>${escHtml(d.toName)}</b>${d.toPhone?` | Ph: ${escHtml(d.toPhone)}`:''}</span><span>Invoice No: <b>${escHtml(d.billNo)}</b></span></div>
${tableNo?`<div style="display:flex;justify-content:space-between;"><span>Table: <b>#${escHtml(tableNo)}</b></span><span>Date: ${fmtDate(d.billDate)}</span></div>`:`<div>Date: ${fmtDate(d.billDate)}</div>`}
<div style="font-size:10px;margin-top:2px;">${escHtml(d.fromName)}${d.fromPhone?' | '+escHtml(d.fromPhone):''}</div>
${d.fromAddr?`<div style="font-size:9px;color:#555;margin-top:1px;">${escHtml(d.fromAddr).replace(/\n/g,' | ')}</div>`:''}
${d.fromEmail?`<div style="font-size:9px;color:#555;">${escHtml(d.fromEmail)}</div>`:''}
${dash}
<table style="width:100%;border-collapse:collapse;"><thead><tr style="border-bottom:1px dashed #555;"><th style="padding:2px 3px;text-align:left;font-size:10px;">Item</th><th style="padding:2px 3px;text-align:right;font-size:10px;">Price</th><th style="padding:2px 3px;text-align:center;font-size:10px;">Qty</th><th style="padding:2px 3px;text-align:right;font-size:10px;">Total</th></tr></thead><tbody>${items||'<tr><td colspan="4" style="text-align:center;padding:5px;color:#aaa;">No items</td></tr>'}</tbody></table>
${dash}
<div style="display:flex;justify-content:space-between;padding:1px 0;"><span>Sub-Total:</span><span>${d.sym}${d.subtotal.toFixed(2)}</span></div>
${cgst>0?`<div style="display:flex;justify-content:space-between;padding:1px 0;"><span>CGST: ${cgst}%</span><span>${d.sym}${cgstAmt.toFixed(2)}</span></div>`:''}
${sgst>0?`<div style="display:flex;justify-content:space-between;padding:1px 0;"><span>SGST: ${sgst}%</span><span>${d.sym}${sgstAmt.toFixed(2)}</span></div>`:''}
${d.taxPct>0&&cgst===0&&sgst===0?`<div style="display:flex;justify-content:space-between;padding:1px 0;"><span>Tax ${d.taxPct}%</span><span>${d.sym}${d.taxAmt.toFixed(2)}</span></div>`:''}
${d.discount>0?`<div style="display:flex;justify-content:space-between;padding:1px 0;"><span>Discount</span><span>-${d.sym}${d.discount.toFixed(2)}</span></div>`:''}
${dash}
<div style="display:flex;justify-content:space-between;align-items:center;"><span>Mode: <b>${escHtml(mode||'-')}</b></span><span style="font-size:13px;font-weight:900;">Total: ${d.sym}${d.total.toFixed(2)}</span></div>
${dash}
${d.notes?`<div style="font-size:10px;color:#555;text-align:center;margin-bottom:3px;">${escHtml(d.notes)}</div>`:''}
<div style="text-align:center;font-size:11px;font-weight:700;margin-top:5px;">** SAVE PAPER SAVE NATURE !!</div>
<div style="text-align:center;font-size:10px;color:#666;margin-top:2px;">Time: ${nowTime()}</div>
${dash}
<div style="text-align:center;font-size:9px;color:#888;">Powered by BillX</div>
</div>`;}

// ─── RENDERER 2: Driver Salary / Helper (formal letter style) ─────────────────
function renderPayslip(d){
    const style = d.td.template_style || '1';
    if (style === '2') {
    // Style 2: Traditional payslip table (earnings vs deductions)
    const c2=TYPE_COLORS[d.type]||'#1565c0';
    const month=d.td.salary_month||fmtDate(d.billDate).replace(/\d{2} /,'');
    const empName=d.td.employer_name||d.fromName;
    const earnings=d.items.filter((_,i)=>i%2===0);
    const deductions=d.items.filter((_,i)=>i%2!==0);
    const maxR=Math.max(earnings.length,deductions.length,1);
    const totalDed=deductions.reduce((s,it)=>s+it.amount,0);
    const rows=Array.from({length:maxR},(_,i)=>`<tr style="border-bottom:1px solid #e0e0e0;"><td style="padding:5px 8px;font-size:11px;">${earnings[i]?escHtml(earnings[i].description):'&nbsp;'}</td><td style="padding:5px 8px;text-align:right;font-size:11px;font-weight:600;">${earnings[i]?d.sym+earnings[i].amount.toFixed(2):'&nbsp;'}</td><td style="padding:5px 8px;border-left:2px solid #ddd;font-size:11px;">${deductions[i]?escHtml(deductions[i].description):'&nbsp;'}</td><td style="padding:5px 8px;text-align:right;font-size:11px;font-weight:600;color:#e53935;">${deductions[i]?'-'+d.sym+deductions[i].amount.toFixed(2):'&nbsp;'}</td></tr>`).join('');
    return `<div style="font-family:Arial,sans-serif;background:#fff;font-size:12px;color:#222;border:1px solid #ccc;max-width:580px;box-shadow:0 2px 10px rgba(0,0,0,.1);">
<div style="background:${c2};color:#fff;padding:14px 20px;display:flex;justify-content:space-between;align-items:center;">
<div><div style="font-size:18px;font-weight:700;">${escHtml(d.fromName)}</div>${d.fromAddr?`<div style="font-size:10px;opacity:.8;">${escHtml(d.fromAddr).replace(/\n/g,' | ')}</div>`:''}${d.fromPhone?`<div style="font-size:10px;opacity:.8;">📞 ${escHtml(d.fromPhone)}</div>`:''}${d.fromEmail?`<div style="font-size:10px;opacity:.8;">${escHtml(d.fromEmail)}</div>`:''}</div>
<div style="text-align:right;"><div style="font-size:14px;font-weight:900;letter-spacing:1px;">SALARY SLIP</div><div style="font-size:10px;opacity:.85;">Pay Period: ${escHtml(month)}</div><div style="font-size:10px;opacity:.85;">#${escHtml(d.billNo)}</div></div>
</div>
<div style="background:#e8eaf6;padding:8px 20px;display:flex;justify-content:space-between;border-bottom:2px solid ${c2};font-size:11px;">
<div><span style="color:#666;display:block;font-size:10px;text-transform:uppercase;">Employee</span><span style="font-size:14px;font-weight:700;">${escHtml(d.toName)}</span>${d.toPhone?`<span style="font-size:10px;color:#666;display:block;">${escHtml(d.toPhone)}</span>`:''}${d.toAddr?`<span style="font-size:10px;color:#666;display:block;">${escHtml(d.toAddr).replace(/\n/g,', ')}</span>`:''}${d.toEmail?`<span style="font-size:10px;color:#666;display:block;">${escHtml(d.toEmail)}</span>`:''}</div>
<div style="text-align:right;"><span style="color:#666;display:block;font-size:10px;text-transform:uppercase;">Employer</span><span style="font-size:13px;font-weight:700;">${escHtml(empName)}</span><span style="font-size:10px;color:#666;display:block;">${fmtDate(d.billDate)}</span></div>
</div>
<table style="width:100%;border-collapse:collapse;">
<thead><tr style="background:#f3f3f3;"><th style="padding:6px 8px;text-align:left;font-size:11px;color:${c2};">Earnings</th><th style="padding:6px 8px;text-align:right;font-size:11px;color:${c2};">Amount</th><th style="padding:6px 8px;text-align:left;font-size:11px;color:#e53935;border-left:2px solid #ddd;">Deductions</th><th style="padding:6px 8px;text-align:right;font-size:11px;color:#e53935;">Amount</th></tr></thead>
<tbody>${rows}</tbody>
</table>
<div style="padding:10px 20px;background:#f8f8f8;border-top:2px solid #ddd;display:flex;justify-content:space-between;align-items:center;font-size:11px;">
<span>Total Deductions: <b style="color:#e53935;">${d.sym}${totalDed.toFixed(2)}</b></span>
</div>
<div style="background:${c2};color:#fff;padding:10px 20px;display:flex;justify-content:space-between;font-size:14px;font-weight:900;"><span>NET SALARY</span><span>${d.sym}${d.total.toFixed(2)}</span></div>
<div style="padding:12px 20px;display:flex;justify-content:space-between;font-size:11px;border-top:1px solid #eee;"><div><div style="border-top:1px solid #555;width:120px;padding-top:4px;margin-top:20px;">Employee Signature</div></div><div><div style="border-top:1px solid #555;width:120px;padding-top:4px;margin-top:20px;">Authorised Signatory</div></div></div>
</div>`;
    }
    const c=TYPE_COLORS[d.type]||'#1565c0';
    const month=d.td.salary_month||fmtDate(d.billDate).replace(/\d{2} /,'');
    const vehicleNo=d.td.vehicle_number||'__________';
    const empName=d.td.employer_name||d.fromName;
    const designation=d.td.designation||'Driver';
    return`<div style="font-family:Arial,sans-serif;background:#fff;padding:24px 28px;font-size:12px;color:#111;border:1px solid #ccc;max-width:520px;line-height:1.7;">
<div style="text-align:right;font-size:12px;color:#333;margin-bottom:8px;">Date: ${fmtDate(d.billDate)}</div>
${d.fromAddr||d.fromPhone||d.fromEmail?`<div style="font-size:11px;margin-bottom:12px;border-left:3px solid ${c};padding-left:8px;"><b>${escHtml(d.fromName)}</b>${d.fromAddr?`<br>${escHtml(d.fromAddr).replace(/\n/g,', ')}`:''} ${d.fromPhone?`<br>Ph: ${escHtml(d.fromPhone)}`:''}${d.fromEmail?`<br>${escHtml(d.fromEmail)}`:''}</div>`:''}
<div style="text-align:center;font-size:14px;font-weight:700;text-decoration:underline;margin-bottom:14px;">${TYPE_LABELS[d.type]||'Salary Receipt'}</div>
<p style="margin:0 0 12px;text-align:justify;">This is to certify that Mr./Ms. <b>${escHtml(empName)}</b> have paid <b>${d.sym}${d.total.toFixed(2)}</b> to ${designation} Mr/Ms <b>${escHtml(d.toName)}</b> towards salary of the month of <b>${escHtml(month)}</b> (Acknowledged receipt enclosed). I also declare that the ${designation.toLowerCase()} is exclusively utilized for official purpose only</p>
<p style="margin:0 0 16px;text-align:justify;">Please reimburse the above amount. I further declare that what is stated above is correct and true.</p>
${d.items.length>0?`<table style="width:100%;border-collapse:collapse;margin-bottom:14px;font-size:11px;"><thead><tr style="border-bottom:1px solid #ccc;"><th style="padding:4px 6px;text-align:left;">Description</th><th style="padding:4px 6px;text-align:right;">Amount</th></tr></thead><tbody>${d.items.map(it=>`<tr style="border-bottom:1px solid #eee;"><td style="padding:4px 6px;">${escHtml(it.description||'-')}</td><td style="padding:4px 6px;text-align:right;font-weight:600;">${d.sym}${it.amount.toFixed(2)}</td></tr>`).join('')}</tbody></table>`:''}
<div style="display:flex;justify-content:space-between;margin-bottom:4px;"><div><b>Vehicle Number:</b> ${escHtml(vehicleNo)}</div><div><b>Date:</b> ${fmtDate(d.billDate)}</div></div>
<div style="display:flex;justify-content:space-between;margin-bottom:8px;"><div><b>Driver Name:</b> ${escHtml(d.toName)}</div><div><b>Employee Name:</b> ${escHtml(empName)}</div></div>
${d.toPhone||d.toAddr||d.toEmail?`<div style="font-size:11px;margin-bottom:8px;color:#444;">${d.toPhone?`Ph: ${escHtml(d.toPhone)}  `:''}${d.toAddr?`Address: ${escHtml(d.toAddr).replace(/\n/g,', ')}  `:''}${d.toEmail?escHtml(d.toEmail):''}</div>`:''}
<div style="font-weight:700;margin-bottom:4px;">Revenue Stamp</div>
<div style="width:64px;height:64px;border:1px dashed #aaa;display:flex;align-items:center;justify-content:center;font-size:9px;color:#aaa;text-align:center;padding:4px;">Revenue<br>Stamp</div>
${d.notes?`<div style="margin-top:12px;font-size:11px;color:#555;border-top:1px dashed #ddd;padding-top:8px;">${escHtml(d.notes)}</div>`:''}
<div style="text-align:center;font-size:9px;color:#aaa;margin-top:16px;border-top:1px dashed #ddd;padding-top:6px;">* This is a computer-generated receipt | BillX</div>
</div>`;}

// ─── RENDERER 3: Fuel Receipt (formal two-column) ─────────────────────────────
function renderFuel(d){
    const style = d.td.template_style || '1';
    if (style === '2') {
    // Style 2: Compact pump slip
    const vehicleNo=d.td.vehicle_number||''; const vehicleType=d.td.vehicle_type||''; const payMode=d.td.payment_mode||'Cash';
    const billTime=nowTime();
    const rows=d.items.map(it=>`<div style="display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px dashed #ddd;font-size:12px;"><span>${escHtml(it.description||'Fuel')}</span><span>${it.qty} L × ${d.sym}${it.rate.toFixed(2)}</span><span style="font-weight:700;">${d.sym}${it.amount.toFixed(2)}</span></div>`).join('');
    return `<div style="font-family:'Courier New',monospace;background:#fff;max-width:320px;margin:0 auto;padding:14px 16px;border:1px solid #ccc;font-size:11px;color:#111;">
<div style="text-align:center;border-bottom:2px solid #333;padding-bottom:8px;margin-bottom:8px;">
<div style="font-size:13px;font-weight:700;">${escHtml(d.fromName)}</div>
${d.fromAddr?`<div style="font-size:10px;color:#555;">${escHtml(d.fromAddr).replace(/\n/g,' | ')}</div>`:''}
${d.fromPhone?`<div style="font-size:10px;color:#555;">${escHtml(d.fromPhone)}</div>`:''}
${d.fromEmail?`<div style="font-size:10px;color:#555;">${escHtml(d.fromEmail)}</div>`:''}
<div style="font-size:11px;font-weight:600;margin-top:4px;">FUEL RECEIPT</div>
</div>
<div style="display:flex;justify-content:space-between;margin-bottom:4px;"><span>Receipt#: <b>${escHtml(d.billNo)}</b></span><span>${fmtDate(d.billDate)}</span></div>
<div style="margin-bottom:4px;">Customer: <b>${escHtml(d.toName)}</b>${d.toPhone?` | 📞 ${escHtml(d.toPhone)}`:''}</div>
${d.toAddr?`<div style="margin-bottom:2px;font-size:10px;color:#555;">Address: ${escHtml(d.toAddr).replace(/\n/g,', ')}</div>`:''}
${d.toEmail?`<div style="margin-bottom:2px;font-size:10px;color:#555;">${escHtml(d.toEmail)}</div>`:''}
${vehicleNo?`<div style="margin-bottom:2px;">Vehicle: <b>${escHtml(vehicleNo)}</b>${vehicleType?' ('+escHtml(vehicleType)+')':''}</div>`:''}
<div style="border-top:1px dashed #888;margin:6px 0;"></div>
${rows||`<div style="font-size:11px;color:#aaa;">No fuel items</div>`}
<div style="border-top:1px dashed #888;margin:6px 0;"></div>
${d.discount>0?`<div style="display:flex;justify-content:space-between;"><span>Discount</span><span>-${d.sym}${d.discount.toFixed(2)}</span></div>`:''}
<div style="display:flex;justify-content:space-between;font-size:13px;font-weight:900;border-top:2px solid #111;padding-top:4px;margin-top:4px;"><span>TOTAL</span><span>${d.sym}${d.total.toFixed(2)}</span></div>
<div style="margin-top:4px;font-size:10px;">Payment: ${escHtml(payMode)} | Time: ${billTime}</div>
${d.notes?`<div style="font-size:10px;color:#555;margin-top:4px;">${escHtml(d.notes)}</div>`:''}
<div style="text-align:center;font-size:9px;color:#888;margin-top:8px;border-top:1px dashed #ccc;padding-top:4px;">SAVE FUEL, SECURE THE FUTURE</div>
</div>`;
    }
    const vehicleNo=d.td.vehicle_number||'';const vehicleType=d.td.vehicle_type||'';const fuelType=d.td.fuel_type||'Petrol';const payMode=d.td.payment_mode||'';
    const items=d.items.map(it=>`<tr style="border-bottom:1px solid #e0e0e0;"><td style="padding:6px 8px;font-size:12px;">${d.sym}${it.rate.toFixed(2)}</td><td style="padding:6px 8px;text-align:center;font-size:12px;">${it.qty} lt.</td><td style="padding:6px 8px;text-align:right;font-weight:700;font-size:12px;">${d.sym}${it.amount.toFixed(2)}</td></tr>`).join('');
    return`<div style="font-family:Arial,sans-serif;background:#fff;padding:24px 28px;font-size:12px;color:#111;border:1px solid #ddd;max-width:520px;box-shadow:0 2px 8px rgba(0,0,0,.08);">
<h2 style="font-size:22px;font-weight:700;margin:0 0 16px;border-bottom:2px solid #333;padding-bottom:8px;">Fuel Receipt</h2>
<div style="display:flex;justify-content:space-between;margin-bottom:14px;">
<div></div>
<div style="text-align:right;"><div style="font-weight:700;font-size:12px;margin-bottom:4px;">Receipt Details</div>
<div>Receipt Number: <b>${escHtml(d.billNo)}</b></div>
<div>Date: ${fmtDate(d.billDate)}</div>
<div>Time: ${nowTime()}</div></div>
</div>
<div style="display:flex;justify-content:space-between;margin-bottom:14px;">
<div><div style="font-weight:700;margin-bottom:6px;font-size:12px;">Billed To</div>
<div>Customer Name: <b>${escHtml(d.toName)}</b></div>
${d.toPhone?`<div>Ph: <b>${escHtml(d.toPhone)}</b></div>`:''}
${d.toAddr?`<div style="font-size:11px;color:#555;">Address: ${escHtml(d.toAddr).replace(/\n/g,', ')}</div>`:''}
${d.toEmail?`<div style="font-size:11px;color:#555;">${escHtml(d.toEmail)}</div>`:''}
${vehicleNo?`<div>Vehicle Number: <b>${escHtml(vehicleNo)}</b></div>`:'<div>Vehicle Number: </div>'}
${vehicleType?`<div>Vehicle Type: <b>${escHtml(vehicleType)}</b></div>`:'<div>Vehicle Type: </div>'}
</div>
<div style="text-align:right;"><div style="font-weight:700;margin-bottom:6px;font-size:12px;">Fuel Station Details</div>
<div>Fuel Station Name: <b>${escHtml(d.fromName)}</b></div>
${d.fromAddr?`<div style="max-width:200px;text-align:right;">Fuel Station Address: ${escHtml(d.fromAddr).replace(/\n/g,', ')}</div>`:'<div>Fuel Station Address: </div>'}
${d.fromPhone?`<div>📞 ${escHtml(d.fromPhone)}</div>`:''}
${d.fromEmail?`<div>${escHtml(d.fromEmail)}</div>`:''}
</div>
</div>
${payMode?`<div style="text-align:right;font-weight:700;margin-bottom:10px;">Payment Method: ${escHtml(payMode)}</div>`:'<div style="text-align:right;font-weight:700;margin-bottom:10px;">Payment Method</div>'}
<div style="border:1px solid #ddd;border-radius:4px;overflow:hidden;margin-bottom:12px;">
<div style="background:#f5f5f5;padding:6px 8px;font-weight:700;font-size:12px;border-bottom:1px solid #ddd;">Receipt Summary</div>
<table style="width:100%;border-collapse:collapse;"><thead><tr style="border-bottom:1px solid #ddd;background:#fafafa;"><th style="padding:6px 8px;text-align:left;font-size:11px;">Fuel Rate</th><th style="padding:6px 8px;text-align:center;font-size:11px;">Quantity</th><th style="padding:6px 8px;text-align:right;font-size:11px;">Total Amount</th></tr></thead>
<tbody>${items||`<tr><td style="padding:6px 8px;">${d.sym}</td><td style="padding:6px 8px;text-align:center;">lt.</td><td style="padding:6px 8px;text-align:right;">${d.sym}</td></tr>`}</tbody></table>
</div>
${d.taxPct>0?`<div style="text-align:right;font-size:11px;color:#666;margin-bottom:4px;">Tax ${d.taxPct}%: ${d.sym}${d.taxAmt.toFixed(2)}</div>`:''}
<div style="text-align:right;font-weight:700;font-size:13px;border-top:2px solid #333;padding-top:6px;margin-bottom:14px;">Total: ${d.sym}${d.total.toFixed(2)}</div>
<div style="text-align:center;border-top:1px solid #ddd;padding-top:12px;">
<div style="font-weight:700;font-size:12px;margin-bottom:4px;">THANK YOU ! FOR FUELLING WITH US !</div>
<div style="font-size:11px;color:#555;margin-bottom:4px;">FOR ANY QUERIES AND COMPLAINTS VISIT OUR CUSTOMER CARE</div>
<div style="font-size:11px;font-weight:600;margin-bottom:4px;">SAVE FUEL, SECURE THE FUTURE!</div>
<div style="font-size:11px;color:#888;">TIME: ${nowTime()}</div>
</div>
</div>`;}

// ─── RENDERER 4: Cab / Travel ─────────────────────────────────────────────────
function renderCab(d){
    const style = d.td.template_style || '1';
    if (style === '2') {
    // Style 2: Clean white cab receipt
    const vehicleNo=d.td.vehicle_number||''; const driverName=d.td.driver_name||'';
    const pickup=d.td.pickup||''; const drop=d.td.drop||'';
    const rows=d.items.map((it,i)=>`<tr style="background:${i%2===0?'#fafafa':'#fff'};border-bottom:1px solid #eee;"><td style="padding:5px 8px;font-size:12px;">${escHtml(it.description||'-')}</td><td style="padding:5px 8px;text-align:right;font-weight:700;font-size:12px;">${d.sym}${it.amount.toFixed(2)}</td></tr>`).join('');
    return `<div style="font-family:Arial,sans-serif;background:#fff;font-size:12px;color:#222;border:1px solid #ddd;max-width:400px;box-shadow:0 2px 10px rgba(0,0,0,.1);">
<div style="background:#fff7e6;padding:12px 16px;border-bottom:3px solid #f5a623;">
<div style="display:flex;justify-content:space-between;align-items:flex-start;">
<div><div style="font-size:16px;font-weight:700;">🚕 ${escHtml(d.fromName)}</div>${d.fromAddr?`<div style="font-size:10px;color:#666;">${escHtml(d.fromAddr).replace(/\n/g,' | ')}</div>`:''}${d.fromPhone?`<div style="font-size:11px;color:#666;">Ph: ${escHtml(d.fromPhone)}</div>`:''}${d.fromEmail?`<div style="font-size:10px;color:#666;">${escHtml(d.fromEmail)}</div>`:''}</div>
<div style="text-align:right;"><div style="font-size:12px;font-weight:700;color:#f5a623;">RIDE RECEIPT</div><div style="font-size:11px;color:#666;">${escHtml(d.billNo)}</div><div style="font-size:11px;color:#666;">${fmtDate(d.billDate)}</div></div>
</div>
</div>
<div style="padding:8px 16px;border-bottom:1px solid #eee;background:#fffbf3;">
<div style="font-size:11px;color:#888;text-transform:uppercase;font-weight:600;">Passenger</div>
<div style="font-size:13px;font-weight:700;">${escHtml(d.toName)}</div>${d.toPhone?`<div style="font-size:11px;color:#666;">${escHtml(d.toPhone)}</div>`:''}${d.toAddr?`<div style="font-size:11px;color:#666;">${escHtml(d.toAddr).replace(/\n/g,', ')}</div>`:''}${d.toEmail?`<div style="font-size:11px;color:#666;">${escHtml(d.toEmail)}</div>`:''}
${pickup||drop?`<div style="font-size:11px;color:#555;margin-top:4px;">${pickup?'From: '+escHtml(pickup):''} ${drop?'→ To: '+escHtml(drop):''}</div>`:''}
${vehicleNo?`<div style="font-size:11px;color:#666;margin-top:2px;">Vehicle: ${escHtml(vehicleNo)}${driverName?' | Driver: '+escHtml(driverName):''}</div>`:''}
</div>
<table style="width:100%;border-collapse:collapse;"><thead><tr style="background:#f5f5f5;"><th style="padding:6px 8px;text-align:left;font-size:11px;">Description</th><th style="padding:6px 8px;text-align:right;font-size:11px;">Amount</th></tr></thead>
<tbody>${rows||'<tr><td colspan="2" style="padding:10px;text-align:center;color:#aaa;">No items</td></tr>'}</tbody></table>
<div style="padding:8px 16px;border-top:1px solid #ddd;background:#f9f9f9;">
${d.discount>0?`<div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:4px;"><span>Discount</span><span>-${d.sym}${d.discount.toFixed(2)}</span></div>`:''}
<div style="display:flex;justify-content:space-between;font-size:15px;font-weight:900;color:#f5a623;border-top:2px solid #f5a623;padding-top:6px;"><span>FARE TOTAL</span><span>${d.sym}${d.total.toFixed(2)}</span></div>
</div>
${d.notes?`<div style="padding:6px 16px;font-size:10px;color:#666;border-top:1px solid #eee;">${escHtml(d.notes)}</div>`:''}
<div style="padding:6px;text-align:center;font-size:9px;color:#aaa;background:#fff7e6;">Safe Journey! ⭐ Rate your ride | BillX</div>
</div>`;
    }
    const vehicleNo=d.td.vehicle_number||'';const driverName=d.td.driver_name||'';
    const pickup=d.td.pickup||'';const drop=d.td.drop||'';
    const items=d.items.map(it=>`<div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #333;font-size:12px;"><span>${escHtml(it.description||'-')}</span><span style="font-weight:700;">${d.sym}${it.amount.toFixed(2)}</span></div>`).join('');
    return`<div style="font-family:Arial,sans-serif;background:#1a1a1a;color:#fff;max-width:360px;border-radius:8px;overflow:hidden;font-size:12px;box-shadow:0 4px 16px rgba(0,0,0,.3);">
<div style="background:#f5a623;padding:14px 16px;color:#1a1a1a;">
<div style="font-size:20px;font-weight:900;">🚕 ${escHtml(d.fromName)}</div>
${d.fromPhone?`<div style="font-size:11px;">📞 ${escHtml(d.fromPhone)}</div>`:''}
${d.fromAddr?`<div style="font-size:10px;">${escHtml(d.fromAddr).replace(/\n/g,' | ')}</div>`:''}
${d.fromEmail?`<div style="font-size:10px;">${escHtml(d.fromEmail)}</div>`:''}
</div>
<div style="padding:10px 16px;background:#2a2a2a;border-bottom:1px solid #444;">
<div style="display:flex;justify-content:space-between;"><span style="font-size:11px;color:#f5a623;font-weight:700;">CAB & TRAVEL RECEIPT</span><span style="font-size:11px;color:#aaa;">${fmtDate(d.billDate)}</span></div>
<div style="font-size:11px;color:#aaa;">Receipt#: <b style="color:#fff;">${escHtml(d.billNo)}</b></div>
</div>
<div style="padding:10px 16px;background:#222;border-bottom:1px solid #444;">
<div style="font-size:10px;color:#f5a623;text-transform:uppercase;letter-spacing:.1em;">Passenger</div>
<div style="font-size:14px;font-weight:700;">${escHtml(d.toName)}</div>
${d.toPhone?`<div style="font-size:11px;color:#aaa;">📞 ${escHtml(d.toPhone)}</div>`:''}
${d.toAddr?`<div style="font-size:11px;color:#aaa;">${escHtml(d.toAddr).replace(/\n/g,', ')}</div>`:''}
${d.toEmail?`<div style="font-size:11px;color:#aaa;">${escHtml(d.toEmail)}</div>`:''}
${pickup?`<div style="font-size:11px;color:#aaa;">From: ${escHtml(pickup)}</div>`:''}
${drop?`<div style="font-size:11px;color:#aaa;">To: ${escHtml(drop)}</div>`:''}
${vehicleNo?`<div style="font-size:11px;color:#aaa;">Cab#: ${escHtml(vehicleNo)}${driverName?' | Driver: '+escHtml(driverName):''}</div>`:''}
</div>
<div style="padding:10px 16px;">
${items||'<div style="color:#666;text-align:center;">No items</div>'}
${d.taxPct>0?`<div style="display:flex;justify-content:space-between;padding:4px 0;font-size:11px;color:#aaa;"><span>Tax ${d.taxPct}%</span><span>${d.sym}${d.taxAmt.toFixed(2)}</span></div>`:''}
${d.discount>0?`<div style="display:flex;justify-content:space-between;padding:4px 0;font-size:11px;color:#aaa;"><span>Discount</span><span>-${d.sym}${d.discount.toFixed(2)}</span></div>`:''}
</div>
<div style="background:#f5a623;color:#1a1a1a;padding:12px 16px;display:flex;justify-content:space-between;font-size:16px;font-weight:900;"><span>FARE TOTAL</span><span>${d.sym}${d.total.toFixed(2)}</span></div>
${d.notes?`<div style="padding:8px 16px;font-size:10px;color:#aaa;border-top:1px solid #444;">${escHtml(d.notes)}</div>`:''}
<div style="text-align:center;padding:8px;font-size:9px;color:#666;">Safe Journey! | BillX</div>
</div>`;}

// ─── RENDERER 5: Official Receipt (rent, lta) ─────────────────────────────────
function renderOfficial(d){
    const style = d.td.template_style || '1';
    if (style === '2') {
    // Style 2: Simple acknowledgment receipt
    const c2=TYPE_COLORS[d.type]||'#2e7d32'; const label=TYPE_LABELS[d.type]||'Receipt';
    const pan=d.td.pan_number||''; const prop=d.td.property_info||'';
    return `<div style="font-family:Arial,sans-serif;background:#fff;font-size:12px;color:#222;border:1px solid #ccc;max-width:560px;padding:20px 24px;">
<div style="text-align:center;margin-bottom:16px;border-bottom:2px solid ${c2};padding-bottom:12px;">
<div style="font-size:16px;font-weight:700;">${escHtml(d.fromName)}</div>
${d.fromAddr?`<div style="font-size:10px;color:#666;">${escHtml(d.fromAddr).replace(/\n/g,' | ')}</div>`:''}
${d.fromPhone?`<div style="font-size:10px;color:#666;">Ph: ${escHtml(d.fromPhone)}</div>`:''}
${d.fromEmail?`<div style="font-size:10px;color:#666;">${escHtml(d.fromEmail)}</div>`:''}
</div>
<div style="text-align:center;margin-bottom:14px;">
<div style="font-size:18px;font-weight:700;text-decoration:underline;letter-spacing:1px;">${label.toUpperCase()}</div>
<div style="font-size:11px;color:#666;">Receipt No: ${escHtml(d.billNo)} | Date: ${fmtDate(d.billDate)}</div>
</div>
<div style="background:#f9f9f9;border:1px solid #e0e0e0;padding:12px;margin-bottom:14px;border-radius:4px;">
<p style="margin:0 0 8px;font-size:13px;">Received with thanks a sum of <b style="font-size:14px;color:${c2};">${d.sym}${d.total.toFixed(2)}</b> (${escHtml(d.toName)})</p>
${d.toPhone?`<p style="margin:0 0 4px;font-size:11px;color:#555;">Ph: ${escHtml(d.toPhone)}</p>`:''}${d.toAddr?`<p style="margin:0 0 4px;font-size:11px;color:#555;">Address: ${escHtml(d.toAddr).replace(/\n/g,', ')}</p>`:''}${d.toEmail?`<p style="margin:0 0 4px;font-size:11px;color:#555;">${escHtml(d.toEmail)}</p>`:''}
<p style="margin:0;font-size:11px;color:#555;">Towards: ${d.items.map(it=>escHtml(it.description)).join(', ')||escHtml(label)}</p>
${prop?`<p style="margin:6px 0 0;font-size:11px;color:#555;">Property/Ref: ${escHtml(prop)}</p>`:''}
${pan?`<p style="margin:4px 0 0;font-size:11px;color:#555;">PAN: ${escHtml(pan)}</p>`:''}
</div>
${d.notes?`<div style="font-size:11px;color:#555;font-style:italic;margin-bottom:12px;">Note: ${escHtml(d.notes)}</div>`:''}
<div style="display:flex;justify-content:space-between;margin-top:20px;font-size:11px;">
<div><div style="border-top:1px solid #555;width:120px;padding-top:4px;margin-top:24px;">Receiver</div></div>
<div><div style="border-top:1px solid #555;width:120px;padding-top:4px;margin-top:24px;">Issuer's Signature</div></div>
</div>
<div style="text-align:center;font-size:9px;color:#aaa;margin-top:12px;border-top:1px dashed #ddd;padding-top:6px;">Computer-generated receipt | BillX</div>
</div>`;
    }
    const c=TYPE_COLORS[d.type]||'#2e7d32';const label=TYPE_LABELS[d.type]||'Receipt';
    const pan=d.td.pan_number||'';const prop=d.td.property_info||'';
    const items=d.items.map(it=>`<div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px dotted #bbb;font-size:12px;"><span>${escHtml(it.description||'-')}</span><span style="font-weight:600;">${d.sym}${it.amount.toFixed(2)}</span></div>`).join('');
    return`<div style="font-family:Georgia,serif;background:#fff;padding:24px 28px;font-size:12px;color:#222;border:2px solid ${c};max-width:600px;position:relative;box-shadow:0 2px 12px rgba(0,0,0,.1);">
<div style="position:absolute;top:10px;right:12px;font-size:64px;color:${c};opacity:.06;font-weight:900;pointer-events:none;">ORIGINAL</div>
<div style="text-align:center;margin-bottom:12px;">
<div style="font-size:11px;letter-spacing:3px;text-transform:uppercase;color:${c};font-weight:700;">— Official —</div>
<div style="font-size:22px;font-weight:900;letter-spacing:1px;color:${c};">${label.toUpperCase()}</div>
<div style="font-size:16px;font-weight:700;">${escHtml(d.fromName)}</div>
${d.fromAddr?`<div style="font-size:11px;color:#555;">${escHtml(d.fromAddr).replace(/\n/g,' | ')}</div>`:''}
${d.fromPhone?`<div style="font-size:11px;color:#555;">📞 ${escHtml(d.fromPhone)}</div>`:''}
${d.fromEmail?`<div style="font-size:11px;color:#555;">${escHtml(d.fromEmail)}</div>`:''}
${pan?`<div style="font-size:11px;color:#555;">PAN: <b>${escHtml(pan)}</b></div>`:''}
</div>
<div style="border-top:3px double ${c};border-bottom:3px double ${c};padding:8px 0;margin-bottom:12px;">
<div style="display:flex;justify-content:space-between;"><span>Receipt No.: <b>${escHtml(d.billNo)}</b></span><span>Date: <b>${fmtDate(d.billDate)}</b></span></div>
</div>
<div style="margin-bottom:12px;padding:10px;background:#f9fdf9;border-left:4px solid ${c};">
<div style="font-size:11px;color:#666;margin-bottom:4px;">Received with thanks from:</div>
<div style="font-size:15px;font-weight:700;">${escHtml(d.toName)}</div>
${d.toAddr?`<div style="font-size:11px;color:#555;">${escHtml(d.toAddr).replace(/\n/g,', ')}</div>`:''}
${d.toPhone?`<div style="font-size:11px;color:#555;">Contact: ${escHtml(d.toPhone)}</div>`:''}
${d.toEmail?`<div style="font-size:11px;color:#555;">${escHtml(d.toEmail)}</div>`:''}
</div>
${prop?`<div style="font-size:11px;color:#555;margin-bottom:8px;">Property / Office: <b>${escHtml(prop)}</b></div>`:''}
<div style="margin-bottom:8px;font-size:11px;color:#555;text-transform:uppercase;letter-spacing:.05em;font-weight:600;">Particulars</div>
${items||`<div style="padding:6px 0;border-bottom:1px dotted #bbb;font-size:12px;color:#aaa;">No items specified</div>`}
<div style="margin-top:12px;border-top:1px solid #ccc;padding-top:8px;">
${d.taxPct>0?`<div style="display:flex;justify-content:space-between;font-size:11px;color:#666;margin-bottom:2px;"><span>Tax ${d.taxPct}%</span><span>${d.sym}${d.taxAmt.toFixed(2)}</span></div>`:''}
${d.discount>0?`<div style="display:flex;justify-content:space-between;font-size:11px;color:#666;margin-bottom:2px;"><span>Discount</span><span>-${d.sym}${d.discount.toFixed(2)}</span></div>`:''}
<div style="display:flex;justify-content:space-between;font-size:16px;font-weight:900;color:${c};border-top:2px solid ${c};padding-top:6px;margin-top:4px;"><span>Total Amount Received</span><span>${d.sym}${d.total.toFixed(2)}</span></div>
</div>
${d.notes?`<div style="margin-top:10px;font-size:11px;color:#555;font-style:italic;border-top:1px dashed #ccc;padding-top:8px;">Note: ${escHtml(d.notes)}</div>`:''}
<div style="margin-top:20px;display:flex;justify-content:space-between;font-size:11px;">
<div><div style="border-top:1px solid #555;width:130px;padding-top:4px;margin-top:30px;">Receiver's Signature</div></div>
<div style="text-align:right;"><div style="border-top:1px solid #555;width:130px;padding-top:4px;margin-top:30px;">Issuer's Signature</div></div>
</div>
<div style="text-align:center;font-size:9px;color:#aaa;margin-top:12px;border-top:1px dashed #ddd;padding-top:6px;">Generated by BillX | Computer-generated receipt</div>
</div>`;}

// ─── RENDERER 6: Medical Bill (hospital invoice style) ────────────────────────
function renderMedical(d){
    const style = d.td.template_style || '1';
    if (style === '2') {
    // Style 2: Simple clinic / pharmacy receipt
    const doctor=d.td.doctor_name||''; const issue=d.td.patient_issue||'';
    const c2='#2e7d32';
    const rows=d.items.map((it,i)=>`<tr style="background:${i%2===0?'#f1f8e9':'#fff'};border-bottom:1px solid #c8e6c9;"><td style="padding:5px 8px;font-size:12px;">${escHtml(it.description||'-')}</td><td style="padding:5px 8px;text-align:center;font-size:12px;">${it.qty}</td><td style="padding:5px 8px;text-align:right;font-size:12px;">${d.sym}${it.rate.toFixed(2)}</td><td style="padding:5px 8px;text-align:right;font-weight:700;font-size:12px;">${d.sym}${it.amount.toFixed(2)}</td></tr>`).join('');
    return `<div style="font-family:Arial,sans-serif;background:#fff;font-size:12px;color:#111;border:2px solid ${c2};max-width:560px;overflow:hidden;">
<div style="background:${c2};color:#fff;padding:12px 16px;text-align:center;">
<div style="font-size:18px;font-weight:700;">✚ ${escHtml(d.fromName)}</div>
${d.fromAddr?`<div style="font-size:10px;opacity:.85;margin-top:2px;">${escHtml(d.fromAddr).replace(/\n/g,' | ')}</div>`:''}
${d.fromPhone?`<div style="font-size:10px;opacity:.85;">Ph: ${escHtml(d.fromPhone)}</div>`:''}
${d.fromEmail?`<div style="font-size:10px;opacity:.85;">${escHtml(d.fromEmail)}</div>`:''}
</div>
<div style="background:#e8f5e9;padding:8px 14px;border-bottom:1px solid #c8e6c9;display:flex;justify-content:space-between;font-size:11px;">
<div><span style="font-weight:700;display:block;">Patient: ${escHtml(d.toName)}</span>${d.toPhone?`<span>Ph: ${escHtml(d.toPhone)}</span>`:''}${d.toAddr?`<span style="display:block;font-size:10px;color:#555;">${escHtml(d.toAddr).replace(/\n/g,', ')}</span>`:''}${d.toEmail?`<span style="display:block;font-size:10px;color:#555;">${escHtml(d.toEmail)}</span>`:''}${issue?`<span style="display:block;">Complaint: ${escHtml(issue)}</span>`:''}</div>
<div style="text-align:right;"><span style="display:block;">Receipt #: <b>${escHtml(d.billNo)}</b></span><span style="display:block;">Date: ${fmtDate(d.billDate)}</span>${doctor?`<span style="display:block;">Dr: ${escHtml(doctor)}</span>`:''}</div>
</div>
<table style="width:100%;border-collapse:collapse;"><thead><tr style="background:${c2};color:#fff;"><th style="padding:6px 8px;text-align:left;font-size:11px;">Service / Medicine</th><th style="padding:6px 8px;text-align:center;font-size:11px;">Qty</th><th style="padding:6px 8px;text-align:right;font-size:11px;">Rate</th><th style="padding:6px 8px;text-align:right;font-size:11px;">Amount</th></tr></thead>
<tbody>${rows||`<tr><td colspan="4" style="padding:10px;text-align:center;color:#aaa;">No services</td></tr>`}</tbody></table>
<div style="padding:8px 14px;border-top:1px solid #c8e6c9;background:#f9fbe7;">
${d.taxPct>0?`<div style="display:flex;justify-content:space-between;font-size:11px;"><span>Tax ${d.taxPct}%</span><span>${d.sym}${d.taxAmt.toFixed(2)}</span></div>`:''}
${d.discount>0?`<div style="display:flex;justify-content:space-between;font-size:11px;"><span>Discount</span><span style="color:#e53935;">-${d.sym}${d.discount.toFixed(2)}</span></div>`:''}
<div style="display:flex;justify-content:space-between;font-size:14px;font-weight:900;color:${c2};border-top:2px solid ${c2};padding-top:6px;margin-top:4px;"><span>Total Amount</span><span>${d.sym}${d.total.toFixed(2)}</span></div>
</div>
${d.notes?`<div style="padding:6px 14px;font-size:11px;color:#555;">${escHtml(d.notes)}</div>`:''}
<div style="background:${c2};color:#fff;padding:6px 14px;text-align:center;font-size:10px;">Get Well Soon 🙏 | ${escHtml(d.fromName)}</div>
</div>`;
    }
    const doctor=d.td.doctor_name||'';const patId=d.td.patient_id||'';
    const issue=d.td.patient_issue||'';const guardian=d.td.guardian_name||'';
    const admitDate=d.td.admit_date?fmtDate(d.td.admit_date):'';
    const roomCat=d.td.room_category||'Single';
    const age=d.td.patient_age||'';const insurance=d.td.insurance||'Yes';
    const cgst=parseFloat(d.td.cgst_pct)||0;const sgst=parseFloat(d.td.sgst_pct)||0;
    const cgstAmt=d.subtotal*cgst/100;const sgstAmt=d.subtotal*sgst/100;
    const taxable=d.subtotal;const net=d.subtotal+cgstAmt+sgstAmt-d.discount;
    const items=d.items.map(it=>`<tr><td style="padding:5px 8px;font-size:12px;">${escHtml(it.description||'-')}</td><td style="padding:5px 8px;text-align:right;font-size:12px;">${d.sym}${it.rate.toFixed(2)}</td><td style="padding:5px 8px;text-align:right;font-weight:700;font-size:12px;">${d.sym}${it.amount.toFixed(2)}</td></tr>`).join('');
    return`<div style="font-family:Arial,sans-serif;background:#fff;font-size:12px;color:#111;border:1px solid #ccc;max-width:580px;">
<div style="padding:10px 14px;border-bottom:1px solid #ddd;">
<div style="display:flex;justify-content:space-between;align-items:flex-start;">
<div><div style="font-size:16px;font-weight:700;">${escHtml(d.fromName)}</div>${d.fromAddr?`<div style="font-size:11px;color:#555;">${escHtml(d.fromAddr).replace(/\n/g,' | ')}</div>`:''}</div>
<div style="text-align:right;"><div style="font-size:11px;font-weight:600;">Invoice No: <b>${escHtml(d.billNo)}</b></div></div>
</div>
</div>
<div style="padding:8px 14px;border-bottom:1px solid #ddd;">
<div style="font-weight:700;margin-bottom:4px;">Hospital details:</div>
${d.fromPhone?`<div style="font-size:11px;">Contact Details: ${escHtml(d.fromPhone)}</div>`:''}
${d.fromEmail?`<div style="font-size:11px;">${escHtml(d.fromEmail)}</div>`:''}
<div style="font-size:11px;">Discharge Date:</div>
<div style="font-size:11px;">${fmtDate(d.billDate)}</div>
</div>
<div style="padding:8px 14px;border-bottom:1px solid #ddd;">
<div style="font-weight:700;margin-bottom:6px;">Patient Information</div>
<table style="width:100%;border-collapse:collapse;font-size:11px;">
<tr>
<td style="padding:3px 6px;"><b>Patient Name:</b> ${escHtml(d.toName)}</td>
<td style="padding:3px 6px;"><b>Patient Issue:</b> ${escHtml(issue)}</td>
<td style="padding:3px 6px;"><b>Address:</b> ${escHtml(d.toAddr||'')}</td>
</tr>
<tr>
<td style="padding:3px 6px;"><b>Guardian Name:</b> ${escHtml(guardian)}</td>
<td style="padding:3px 6px;"><b>Admit Date:</b><br>${admitDate}</td>
<td style="padding:3px 6px;"><b>Mobile:</b> ${escHtml(d.toPhone||'')}${d.toEmail?`<br><b>Email:</b> ${escHtml(d.toEmail)}`:''}</td>
</tr>
<tr>
<td style="padding:3px 6px;"><b>Insurance Avl:</b><br>${escHtml(insurance)}</td>
<td style="padding:3px 6px;"><b>Age:</b> ${escHtml(age)}</td>
<td></td>
</tr>
<tr>
<td style="padding:3px 6px;"><b>Consultant:</b> ${escHtml(doctor)}</td>
<td style="padding:3px 6px;"><b>Room Category:</b><br>${escHtml(roomCat)}</td>
<td>${patId?`<span style="padding:3px 6px;font-size:10px;"><b>Patient ID:</b> ${escHtml(patId)}</span>`:''}</td>
</tr>
</table>
</div>
<table style="width:100%;border-collapse:collapse;font-size:12px;">
<thead><tr style="background:#f5f5f5;border-bottom:1px solid #ccc;border-top:1px solid #ccc;"><th style="padding:6px 8px;text-align:left;">Details</th><th style="padding:6px 8px;text-align:right;width:80px;">Price</th><th style="padding:6px 8px;text-align:right;width:80px;">Amount</th></tr></thead>
<tbody>${items||`<tr><td style="padding:8px;color:#aaa;text-align:center;" colspan="3">No services added</td></tr>`}</tbody>
</table>
<div style="padding:8px 14px;border-top:1px solid #ddd;display:flex;justify-content:space-between;">
<div>
<div style="font-weight:700;margin-bottom:4px;">Pay By</div>
<div style="font-size:11px;">Amount: ${d.sym}${d.total.toFixed(2)}</div>
</div>
<div style="text-align:right;font-size:11px;">
<div>Tax: ${d.taxPct}%</div>
${cgst>0?`<div>CGST: ${cgst}% - ${d.sym}${cgstAmt.toFixed(2)}</div>`:'<div>CGST: 0% - ${d.sym}0.00</div>'}
${sgst>0?`<div>SGST: ${sgst}% - ${d.sym}${sgstAmt.toFixed(2)}</div>`:'<div>SGST: 0% - ${d.sym}0.00</div>'}
<div><b>Taxable Amount: ${d.sym}${taxable.toFixed(2)}</b></div>
<div><b>Net Amount: ${net.toFixed(2)}</b></div>
<div><b>Total Amount: ${d.sym}${d.total.toFixed(2)}</b></div>
</div>
</div>
${d.notes?`<div style="padding:8px 14px;border-top:1px solid #ddd;font-size:11px;"><b>Remark:</b><br>${escHtml(d.notes)}</div>`:''}
<div style="padding:8px 14px;border-top:1px solid #ccc;font-size:10px;color:#555;font-style:italic;">* This is a computer-generated invoice. Signature not required. Created on ${fmtDate(d.billDate)} at ${nowTime()}.</div>
</div>`;}

// ─── RENDERER 7: Hotel Folio ──────────────────────────────────────────────────
function renderHotel(d){
    const style = d.td.template_style || '1';
    if (style === '2') {
    // Style 2: Clean white hotel invoice
    const roomNo=d.td.room_number||''; const gstin=d.td.gstin||'';
    const checkin=d.td.checkin_date?fmtDate(d.td.checkin_date):''; const checkout=d.td.checkout_date?fmtDate(d.td.checkout_date):'';
    const rows=d.items.map((it,i)=>`<tr style="background:${i%2===0?'#fafafa':'#fff'};border-bottom:1px solid #e0e0e0;"><td style="padding:7px 10px;font-size:12px;">${escHtml(it.description||'-')}</td><td style="padding:7px 10px;text-align:center;font-size:12px;">${it.qty}</td><td style="padding:7px 10px;text-align:right;font-size:12px;">${d.sym}${it.rate.toFixed(2)}</td><td style="padding:7px 10px;text-align:right;font-weight:700;font-size:12px;">${d.sym}${it.amount.toFixed(2)}</td></tr>`).join('');
    return `<div style="font-family:Arial,sans-serif;background:#fff;font-size:12px;color:#222;border:1px solid #ccc;max-width:620px;box-shadow:0 2px 10px rgba(0,0,0,.1);">
<div style="padding:16px 20px;border-bottom:2px solid #333;display:flex;justify-content:space-between;align-items:flex-start;">
<div><div style="font-size:20px;font-weight:700;">${escHtml(d.fromName)}</div>${d.fromAddr?`<div style="font-size:10px;color:#666;">${escHtml(d.fromAddr).replace(/\n/g,' | ')}</div>`:''}<div style="font-size:10px;color:#666;">${d.fromPhone?'Ph: '+escHtml(d.fromPhone):''}${d.fromEmail?' | '+escHtml(d.fromEmail):''}</div>${gstin?`<div style="font-size:10px;color:#666;">GSTIN: ${escHtml(gstin)}</div>`:''}</div>
<div style="text-align:right;"><div style="font-size:20px;font-weight:900;letter-spacing:1px;">HOTEL INVOICE</div><div style="font-size:11px;color:#666;">Bill #: ${escHtml(d.billNo)}</div><div style="font-size:11px;color:#666;">Date: ${fmtDate(d.billDate)}</div></div>
</div>
<div style="padding:10px 20px;background:#f5f5f5;border-bottom:1px solid #ddd;display:flex;justify-content:space-between;font-size:11px;">
<div><div style="font-weight:600;margin-bottom:2px;">Guest: ${escHtml(d.toName)}</div>${d.toPhone?`<div>Ph: ${escHtml(d.toPhone)}</div>`:''}${d.toAddr?`<div style="font-size:10px;color:#666;">${escHtml(d.toAddr).replace(/\n/g,', ')}</div>`:''}${d.toEmail?`<div style="font-size:10px;color:#666;">${escHtml(d.toEmail)}</div>`:''}</div>
<div style="text-align:right;">${roomNo?`<div>Room: <b>${escHtml(roomNo)}</b></div>`:''}<div>${checkin?'In: '+checkin:''} ${checkout?'→ Out: '+checkout:''}</div></div>
</div>
<table style="width:100%;border-collapse:collapse;"><thead><tr style="background:#333;color:#fff;"><th style="padding:7px 10px;text-align:left;font-size:11px;">Description</th><th style="padding:7px 10px;text-align:center;font-size:11px;width:70px;">Qty</th><th style="padding:7px 10px;text-align:right;font-size:11px;width:80px;">Rate</th><th style="padding:7px 10px;text-align:right;font-size:11px;width:90px;">Amount</th></tr></thead>
<tbody>${rows||'<tr><td colspan="4" style="padding:12px;text-align:center;color:#aaa;">No charges</td></tr>'}</tbody></table>
<div style="padding:10px 20px;background:#f9f9f9;border-top:1px solid #ddd;display:flex;justify-content:flex-end;">
<div style="min-width:220px;font-size:12px;">
${d.subtotal!==d.total?`<div style="display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px solid #ddd;"><span style="color:#666;">Subtotal</span><span>${d.sym}${d.subtotal.toFixed(2)}</span></div>`:''}
${d.taxPct>0?`<div style="display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px solid #ddd;"><span style="color:#666;">Tax ${d.taxPct}%</span><span>${d.sym}${d.taxAmt.toFixed(2)}</span></div>`:''}
${d.discount>0?`<div style="display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px solid #ddd;"><span style="color:#666;">Discount</span><span style="color:#e53935;">-${d.sym}${d.discount.toFixed(2)}</span></div>`:''}
<div style="display:flex;justify-content:space-between;font-size:15px;font-weight:900;border-top:2px solid #333;padding-top:6px;margin-top:4px;"><span>Total Amount</span><span>${d.sym}${d.total.toFixed(2)}</span></div>
</div>
</div>
${d.notes?`<div style="padding:8px 20px;font-size:11px;color:#555;border-top:1px solid #eee;font-style:italic;">${escHtml(d.notes)}</div>`:''}
<div style="padding:8px 20px;border-top:1px solid #ddd;display:flex;justify-content:space-between;font-size:10px;color:#999;"><span>Signature: _______________</span><span>Thank you for staying with us! 🌟</span></div>
</div>`;
    }
    const roomNo=d.td.room_number||'';const gstin=d.td.gstin||'';
    const checkin=d.td.checkin_date?fmtDate(d.td.checkin_date):'';const checkout=d.td.checkout_date?fmtDate(d.td.checkout_date):'';
    const items=d.items.map((it,i)=>`<tr style="background:${i%2===0?'#fdf8ee':'#fff'};border-bottom:1px solid #e8d89a;"><td style="padding:8px 12px;font-size:12px;">${escHtml(it.description||'-')}</td><td style="padding:8px 12px;text-align:center;font-size:12px;">${it.qty}</td><td style="padding:8px 12px;text-align:right;font-size:12px;">${d.sym}${it.rate.toFixed(2)}</td><td style="padding:8px 12px;text-align:right;font-weight:700;font-size:12px;">${d.sym}${it.amount.toFixed(2)}</td></tr>`).join('');
    return`<div style="font-family:Georgia,serif;background:#fffdf5;font-size:12px;color:#333;border:1px solid #c9a84c;box-shadow:0 4px 20px rgba(0,0,0,.15);">
<div style="background:linear-gradient(135deg,#7d5a00,#c9a84c);color:#fff;padding:20px 24px;text-align:center;">
<div style="font-size:10px;letter-spacing:4px;text-transform:uppercase;opacity:.8;margin-bottom:4px;">★ ★ ★</div>
<div style="font-size:22px;font-weight:700;letter-spacing:1px;">${escHtml(d.fromName)}</div>
${d.fromAddr?`<div style="font-size:10px;opacity:.85;margin-top:4px;">${escHtml(d.fromAddr).replace(/\n/g,' | ')}</div>`:''}
${d.fromPhone?`<div style="font-size:10px;opacity:.85;">📞 ${escHtml(d.fromPhone)}</div>`:''}
${d.fromEmail?`<div style="font-size:10px;opacity:.85;">${escHtml(d.fromEmail)}</div>`:''}
${gstin?`<div style="font-size:10px;opacity:.85;">GSTIN: ${escHtml(gstin)}</div>`:''}
<div style="font-size:11px;letter-spacing:3px;text-transform:uppercase;margin-top:8px;opacity:.9;">— ${escHtml(TYPE_LABELS[d.type]||d.type)} —</div>
</div>
<div style="background:#fdf0c0;padding:10px 24px;border-bottom:2px solid #c9a84c;display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;">
<div><span style="font-size:10px;color:#7d5a00;display:block;text-transform:uppercase;">Guest Name</span><span style="font-size:14px;font-weight:700;">${escHtml(d.toName)}</span>${d.toPhone?`<span style="font-size:10px;color:#7d5a00;display:block;">📞 ${escHtml(d.toPhone)}</span>`:''}${d.toAddr?`<span style="font-size:10px;color:#7d5a00;display:block;">${escHtml(d.toAddr).replace(/\n/g,', ')}</span>`:''}${d.toEmail?`<span style="font-size:10px;color:#7d5a00;display:block;">${escHtml(d.toEmail)}</span>`:''}</div>
<div style="text-align:right;"><span style="font-size:10px;color:#7d5a00;display:block;text-transform:uppercase;">Bill Details</span><span style="font-size:12px;display:block;">Bill #: <b>${escHtml(d.billNo)}</b></span><span style="font-size:12px;">Date: ${fmtDate(d.billDate)}</span>${roomNo?`<span style="font-size:11px;display:block;">Room: <b>${escHtml(roomNo)}</b></span>`:''}${checkin?`<span style="font-size:11px;display:block;">In: ${checkin}${checkout?' → '+checkout:''}</span>`:''}</div>
</div>
<table style="width:100%;border-collapse:collapse;"><thead><tr style="background:#c9a84c;color:#fff;"><th style="padding:8px 12px;text-align:left;font-size:11px;">Description</th><th style="padding:8px 12px;text-align:center;font-size:11px;width:70px;">Nights/Qty</th><th style="padding:8px 12px;text-align:right;font-size:11px;width:80px;">Rate</th><th style="padding:8px 12px;text-align:right;font-size:11px;width:90px;">Amount</th></tr></thead>
<tbody>${items||'<tr><td colspan="4" style="padding:12px;text-align:center;color:#aaa;">No charges</td></tr>'}</tbody>
</table>
<div style="padding:10px 24px;background:#fdf8ee;border-top:1px solid #e8d89a;"><div style="display:flex;justify-content:flex-end;"><div style="min-width:220px;">
${d.subtotal!==d.total?`<div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;border-bottom:1px solid #e8d89a;"><span style="color:#777;">Subtotal</span><span>${d.sym}${d.subtotal.toFixed(2)}</span></div>`:''}
${d.taxPct>0?`<div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;border-bottom:1px solid #e8d89a;"><span style="color:#777;">Tax/Service ${d.taxPct}%</span><span>${d.sym}${d.taxAmt.toFixed(2)}</span></div>`:''}
${d.discount>0?`<div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;border-bottom:1px solid #e8d89a;"><span style="color:#777;">Discount</span><span style="color:#e53935;">-${d.sym}${d.discount.toFixed(2)}</span></div>`:''}
<div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;border-top:2px solid #c9a84c;padding-top:6px;margin-top:4px;color:#7d5a00;"><span>Total Amount</span><span>${d.sym}${d.total.toFixed(2)}</span></div>
</div></div></div>
${d.notes?`<div style="padding:8px 24px;font-size:11px;color:#7d5a00;font-style:italic;border-top:1px solid #e8d89a;">${escHtml(d.notes)}</div>`:''}
<div style="background:linear-gradient(135deg,#7d5a00,#c9a84c);color:#fff;padding:10px 24px;display:flex;justify-content:space-between;font-size:10px;"><span>Thank you for your stay! 🌟</span><span>BillX</span></div>
</div>`;}

// ─── RENDERER 8: Gym Invoice ──────────────────────────────────────────────────
function renderGym(d){
    const style = d.td.template_style || '1';
    if (style === '2') {
    // Style 2: Clean membership receipt
    const memberId=d.td.member_id||''; const plan=d.td.plan_name||'';
    const rows=d.items.map((it,i)=>`<tr style="background:${i%2===0?'#f9f9f9':'#fff'};border-bottom:1px solid #e0e0e0;"><td style="padding:6px 10px;font-size:12px;">${escHtml(it.description||'-')}</td><td style="padding:6px 10px;text-align:right;font-weight:700;font-size:12px;">${d.sym}${it.amount.toFixed(2)}</td></tr>`).join('');
    return `<div style="font-family:Arial,sans-serif;background:#fff;font-size:12px;color:#222;border:2px solid #ff6f00;max-width:460px;overflow:hidden;">
<div style="padding:14px 16px;border-bottom:2px solid #ff6f00;">
<div style="display:flex;justify-content:space-between;align-items:flex-start;">
<div><div style="font-size:18px;font-weight:700;">💪 ${escHtml(d.fromName)}</div>${d.fromAddr?`<div style="font-size:10px;color:#666;">${escHtml(d.fromAddr).replace(/\n/g,' | ')}</div>`:''}<div style="font-size:10px;color:#666;">${d.fromPhone?'Ph: '+escHtml(d.fromPhone):''}${d.fromEmail?' | '+escHtml(d.fromEmail):''}</div></div>
<div style="text-align:right;"><div style="font-size:14px;font-weight:900;color:#ff6f00;">GYM RECEIPT</div><div style="font-size:11px;color:#666;">#${escHtml(d.billNo)}</div><div style="font-size:11px;color:#666;">${fmtDate(d.billDate)}</div></div>
</div>
</div>
<div style="padding:8px 16px;background:#fff8f0;border-bottom:1px solid #ffe0b2;font-size:11px;">
<div style="font-weight:700;font-size:13px;">${escHtml(d.toName)}</div>
${d.toPhone?`<div style="color:#666;">${escHtml(d.toPhone)}</div>`:''}
${d.toAddr?`<div style="font-size:10px;color:#666;">${escHtml(d.toAddr).replace(/\n/g,', ')}</div>`:''}
${d.toEmail?`<div style="font-size:10px;color:#666;">${escHtml(d.toEmail)}</div>`:''}
${memberId?`<div>Member ID: <b>${escHtml(memberId)}</b></div>`:''}
${plan?`<div style="color:#ff6f00;font-weight:600;">Plan: ${escHtml(plan)}</div>`:''}
</div>
<table style="width:100%;border-collapse:collapse;"><thead><tr style="background:#ff6f00;color:#fff;"><th style="padding:6px 10px;text-align:left;font-size:11px;">Description</th><th style="padding:6px 10px;text-align:right;font-size:11px;">Amount</th></tr></thead>
<tbody>${rows||'<tr><td colspan="2" style="padding:10px;text-align:center;color:#aaa;">No items</td></tr>'}</tbody></table>
<div style="padding:8px 16px;border-top:1px solid #ffe0b2;background:#fff8f0;">
${d.taxPct>0?`<div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:4px;"><span>Tax ${d.taxPct}%</span><span>${d.sym}${d.taxAmt.toFixed(2)}</span></div>`:''}
${d.discount>0?`<div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:4px;"><span>Discount</span><span style="color:#e53935;">-${d.sym}${d.discount.toFixed(2)}</span></div>`:''}
<div style="display:flex;justify-content:space-between;font-size:15px;font-weight:900;color:#ff6f00;border-top:2px solid #ff6f00;padding-top:6px;margin-top:4px;"><span>TOTAL DUE</span><span>${d.sym}${d.total.toFixed(2)}</span></div>
</div>
${d.notes?`<div style="padding:6px 16px;font-size:10px;color:#666;border-top:1px solid #eee;">${escHtml(d.notes)}</div>`:''}
<div style="padding:6px;text-align:center;font-size:9px;color:#ff6f00;background:#fff8f0;">Train Hard. Stay Strong! 💪 | BillX</div>
</div>`;
    }
    const memberId=d.td.member_id||'';const plan=d.td.plan_name||'';
    const items=d.items.map(it=>`<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #333;font-size:12px;"><span>${escHtml(it.description||'-')}</span><span style="color:#ff6f00;font-weight:700;">${d.sym}${it.amount.toFixed(2)}</span></div>`).join('');
    return`<div style="font-family:Arial,sans-serif;background:#181818;color:#f0f0f0;font-size:12px;box-shadow:0 4px 20px rgba(0,0,0,.4);">
<div style="background:linear-gradient(135deg,#212121,#333);padding:16px 20px;border-bottom:3px solid #ff6f00;">
<div style="display:flex;justify-content:space-between;align-items:center;">
<div><div style="font-size:22px;font-weight:900;">💪 ${escHtml(d.fromName)}</div>${d.fromAddr?`<div style="font-size:10px;color:#aaa;">${escHtml(d.fromAddr).replace(/\n/g,' | ')}</div>`:''}<div style="font-size:10px;color:#aaa;">${d.fromPhone?'📞 '+escHtml(d.fromPhone)+'  ':''}${d.fromEmail?escHtml(d.fromEmail):''}</div></div>
<div style="text-align:right;"><div style="font-size:13px;font-weight:900;color:#ff6f00;letter-spacing:2px;">GYM INVOICE</div><div style="font-size:10px;color:#aaa;">Inv # ${escHtml(d.billNo)}</div><div style="font-size:10px;color:#aaa;">${fmtDate(d.billDate)}</div></div>
</div>
</div>
<div style="padding:10px 20px;background:#242424;border-bottom:1px solid #444;">
<span style="font-size:10px;color:#ff6f00;display:block;text-transform:uppercase;letter-spacing:.1em;">Member</span>
<span style="font-size:14px;font-weight:700;">${escHtml(d.toName)}</span>
${d.toPhone?`<span style="font-size:11px;color:#aaa;display:block;">📞 ${escHtml(d.toPhone)}</span>`:''}
${d.toAddr?`<span style="font-size:11px;color:#aaa;display:block;">${escHtml(d.toAddr).replace(/\n/g,', ')}</span>`:''}
${d.toEmail?`<span style="font-size:11px;color:#aaa;display:block;">${escHtml(d.toEmail)}</span>`:''}
${memberId?`<span style="font-size:11px;color:#aaa;display:block;">Member ID: ${escHtml(memberId)}</span>`:''}
${plan?`<span style="font-size:11px;color:#ff6f00;display:block;">Plan: ${escHtml(plan)}</span>`:''}
</div>
<div style="padding:12px 20px;border-bottom:1px solid #444;">
${items||'<div style="color:#666;text-align:center;padding:8px;">No items</div>'}
</div>
<div style="padding:10px 20px;background:#242424;">
${d.taxPct>0?`<div style="display:flex;justify-content:space-between;font-size:11px;color:#aaa;padding:2px 0;"><span>Tax ${d.taxPct}%</span><span>${d.sym}${d.taxAmt.toFixed(2)}</span></div>`:''}
${d.discount>0?`<div style="display:flex;justify-content:space-between;font-size:11px;color:#aaa;padding:2px 0;"><span>Discount</span><span>-${d.sym}${d.discount.toFixed(2)}</span></div>`:''}
</div>
<div style="background:#ff6f00;color:#fff;padding:12px 20px;display:flex;justify-content:space-between;font-size:16px;font-weight:900;"><span>TOTAL DUE</span><span>${d.sym}${d.total.toFixed(2)}</span></div>
${d.notes?`<div style="padding:8px 20px;font-size:10px;color:#aaa;border-top:1px solid #444;">${escHtml(d.notes)}</div>`:''}
<div style="text-align:center;padding:8px;font-size:9px;color:#555;background:#111;">Stay strong! 💪 | BillX</div>
</div>`;}

// ─── RENDERER 9: Professional Invoice (with GST breakdown) ───────────────────
// (book, internet, ecom, general, recharge, newspaper)
function renderInvoice(d){
    const style = d.td.template_style || '1';
    if (style === '2') {
    // Style 2: Classic letterhead / no color header
    const c2=TYPE_COLORS[d.type]||'#333'; const label=TYPE_LABELS[d.type]||'Invoice';
    const gstin=d.td.gstin||''; const hsn=d.td.hsn_code||'';
    const cgst=parseFloat(d.td.cgst_pct)||0; const sgst=parseFloat(d.td.sgst_pct)||0;
    const cgstAmt=d.subtotal*cgst/100; const sgstAmt=d.subtotal*sgst/100;
    const rows=d.items.map((it,i)=>`<tr><td style="padding:6px 8px;border:1px solid #ddd;font-size:12px;text-align:center;">${i+1}</td><td style="padding:6px 8px;border:1px solid #ddd;font-size:12px;">${escHtml(it.description||'-')}</td>${hsn?`<td style="padding:6px 8px;border:1px solid #ddd;font-size:11px;text-align:center;">${escHtml(hsn)}</td>`:''}<td style="padding:6px 8px;border:1px solid #ddd;text-align:center;font-size:12px;">${it.qty}</td><td style="padding:6px 8px;border:1px solid #ddd;text-align:right;font-size:12px;">${d.sym}${it.rate.toFixed(2)}</td><td style="padding:6px 8px;border:1px solid #ddd;text-align:right;font-weight:700;font-size:12px;">${d.sym}${it.amount.toFixed(2)}</td></tr>`).join('');
    return `<div style="font-family:Arial,sans-serif;background:#fff;font-size:12px;color:#222;border:1px solid #ddd;max-width:680px;">
<div style="padding:16px 24px;border-bottom:3px solid ${c2};">
<div style="display:flex;justify-content:space-between;align-items:flex-start;">
<div><div style="font-size:20px;font-weight:700;color:${c2};">${escHtml(d.fromName)}</div>${d.fromAddr?`<div style="font-size:10px;color:#666;margin-top:2px;">${escHtml(d.fromAddr).replace(/\n/g,' | ')}</div>`:''}<div style="font-size:10px;color:#666;">${d.fromPhone?'Ph: '+escHtml(d.fromPhone):''} ${d.fromEmail?'| '+escHtml(d.fromEmail):''}</div>${gstin?`<div style="font-size:10px;color:#666;">GSTIN: <b>${escHtml(gstin)}</b></div>`:''}</div>
<div style="text-align:right;"><div style="font-size:22px;font-weight:900;letter-spacing:1px;color:${c2};">${label.toUpperCase()}</div><div style="font-size:11px;color:#666;"># ${escHtml(d.billNo)}</div><div style="font-size:11px;color:#666;">Date: ${fmtDate(d.billDate)}</div></div>
</div>
</div>
<div style="padding:10px 24px;background:#fafafa;border-bottom:1px solid #ddd;display:flex;justify-content:space-between;">
<div><div style="font-size:10px;color:#888;text-transform:uppercase;font-weight:600;margin-bottom:2px;">Bill To</div><div style="font-size:14px;font-weight:700;">${escHtml(d.toName)}</div>${d.toAddr?`<div style="font-size:11px;color:#666;">${escHtml(d.toAddr).replace(/\n/g,' | ')}</div>`:''}${d.toPhone?`<div style="font-size:11px;color:#666;">📞 ${escHtml(d.toPhone)}</div>`:''}${d.toEmail?`<div style="font-size:11px;color:#666;">${escHtml(d.toEmail)}</div>`:''}</div>
</div>
<table style="width:100%;border-collapse:collapse;margin:0;"><thead><tr style="background:#f5f5f5;"><th style="padding:7px 8px;border:1px solid #ddd;text-align:center;font-size:11px;width:30px;">#</th><th style="padding:7px 8px;border:1px solid #ddd;text-align:left;font-size:11px;">Description</th>${hsn?`<th style="padding:7px 8px;border:1px solid #ddd;text-align:center;font-size:11px;width:70px;">HSN</th>`:''}<th style="padding:7px 8px;border:1px solid #ddd;text-align:center;font-size:11px;width:50px;">Qty</th><th style="padding:7px 8px;border:1px solid #ddd;text-align:right;font-size:11px;width:80px;">Rate</th><th style="padding:7px 8px;border:1px solid #ddd;text-align:right;font-size:11px;width:90px;">Amount</th></tr></thead>
<tbody>${rows||'<tr><td colspan="6" style="padding:12px;text-align:center;color:#aaa;border:1px solid #ddd;">No items</td></tr>'}</tbody></table>
<div style="padding:12px 24px;display:flex;justify-content:flex-end;background:#fafafa;border-top:1px solid #ddd;">
<div style="min-width:240px;font-size:12px;">
<div style="display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px solid #ddd;"><span style="color:#666;">Subtotal</span><span style="font-weight:600;">${d.sym}${d.subtotal.toFixed(2)}</span></div>
${cgst>0?`<div style="display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px solid #ddd;"><span style="color:#666;">CGST ${cgst}%</span><span>${d.sym}${cgstAmt.toFixed(2)}</span></div>`:''}
${sgst>0?`<div style="display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px solid #ddd;"><span style="color:#666;">SGST ${sgst}%</span><span>${d.sym}${sgstAmt.toFixed(2)}</span></div>`:''}
${d.taxPct>0&&cgst===0&&sgst===0?`<div style="display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px solid #ddd;"><span style="color:#666;">Tax ${d.taxPct}%</span><span>${d.sym}${d.taxAmt.toFixed(2)}</span></div>`:''}
${d.discount>0?`<div style="display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px solid #ddd;"><span style="color:#666;">Discount</span><span style="color:#e53935;">-${d.sym}${d.discount.toFixed(2)}</span></div>`:''}
<div style="display:flex;justify-content:space-between;font-size:15px;font-weight:900;border-top:2px solid ${c2};padding-top:6px;margin-top:4px;color:${c2};"><span>Total</span><span>${d.sym}${d.total.toFixed(2)}</span></div>
</div>
</div>
${d.notes?`<div style="margin:0 24px 12px;padding:8px;background:#fff8e1;border-left:3px solid #ffc107;font-size:11px;color:#555;"><b>Notes:</b> ${escHtml(d.notes)}</div>`:''}
<div style="padding:8px 24px;border-top:1px solid #ddd;display:flex;justify-content:space-between;font-size:10px;color:#999;"><span>Signature: _______________</span><span>Thank you for your business!</span></div>
</div>`;
    }
    if (style === '3') {
    // Style 3: Simple bordered classic invoice
    const label=TYPE_LABELS[d.type]||'Invoice';
    const gstin=d.td.gstin||''; const hsn=d.td.hsn_code||'';
    const cgst=parseFloat(d.td.cgst_pct)||0; const sgst=parseFloat(d.td.sgst_pct)||0;
    const cgstAmt=d.subtotal*cgst/100; const sgstAmt=d.subtotal*sgst/100;
    const rows=d.items.map((it,i)=>`<tr style="background:${i%2===0?'#f9f9f9':'#fff'};border-bottom:1px solid #e0e0e0;"><td style="padding:6px 10px;text-align:center;">${i+1}</td><td style="padding:6px 10px;">${escHtml(it.description||'-')}</td>${hsn?`<td style="padding:6px 10px;text-align:center;">${escHtml(hsn)}</td>`:''}<td style="padding:6px 10px;text-align:center;">${it.qty}</td><td style="padding:6px 10px;text-align:right;">${d.sym}${it.rate.toFixed(2)}</td><td style="padding:6px 10px;text-align:right;font-weight:700;">${d.sym}${it.amount.toFixed(2)}</td></tr>`).join('');
    return `<div style="font-family:'Times New Roman',Times,serif;background:#fff;border:2px solid #222;font-size:12px;color:#111;max-width:680px;">
<div style="border-bottom:2px solid #222;padding:12px 20px;display:flex;justify-content:space-between;align-items:center;">
<div><div style="font-size:18px;font-weight:700;">${escHtml(d.fromName)}</div>${d.fromAddr?`<div style="font-size:10px;color:#444;">${escHtml(d.fromAddr).replace(/\n/g,' | ')}</div>`:''}<div style="font-size:10px;color:#444;">${d.fromPhone?'Ph: '+escHtml(d.fromPhone):''}${d.fromEmail?' | '+escHtml(d.fromEmail):''}</div></div>
<div style="text-align:center;border:2px solid #222;padding:8px 16px;"><div style="font-size:16px;font-weight:900;letter-spacing:2px;">${label.toUpperCase()}</div><div style="font-size:11px;">#${escHtml(d.billNo)}</div><div style="font-size:11px;">${fmtDate(d.billDate)}</div></div>
</div>
<div style="border-bottom:1px solid #ccc;padding:8px 20px;display:flex;justify-content:space-between;font-size:11px;">
<div><b>Bill To:</b> ${escHtml(d.toName)}${d.toAddr?` | ${escHtml(d.toAddr).replace(/\n/g,', ')}`:''}<br><span style="font-size:10px;color:#444;">${d.toPhone?escHtml(d.toPhone):''}${d.toPhone&&d.toEmail?' | ':''}${d.toEmail?escHtml(d.toEmail):''}</span></div>
${gstin?`<div><b>GSTIN:</b> ${escHtml(gstin)}</div>`:''}
</div>
<table style="width:100%;border-collapse:collapse;font-size:12px;"><thead><tr style="background:#222;color:#fff;"><th style="padding:6px 10px;text-align:center;">#</th><th style="padding:6px 10px;text-align:left;">Description</th>${hsn?`<th style="padding:6px 10px;text-align:center;">HSN</th>`:''}<th style="padding:6px 10px;text-align:center;">Qty</th><th style="padding:6px 10px;text-align:right;">Rate</th><th style="padding:6px 10px;text-align:right;">Amount</th></tr></thead>
<tbody>${rows||'<tr><td colspan="6" style="padding:12px;text-align:center;color:#aaa;">No items</td></tr>'}</tbody></table>
<div style="border-top:2px solid #222;padding:10px 20px;display:flex;justify-content:space-between;align-items:flex-end;">
<div style="font-size:11px;">${d.notes?`<b>Notes:</b> ${escHtml(d.notes)}`:'&nbsp;'}</div>
<div style="text-align:right;font-size:12px;">
${d.discount>0?`<div style="display:flex;justify-content:space-between;gap:24px;"><span>Discount</span><span>-${d.sym}${d.discount.toFixed(2)}</span></div>`:''}
${cgst>0?`<div style="display:flex;justify-content:space-between;gap:24px;"><span>CGST ${cgst}%</span><span>${d.sym}${cgstAmt.toFixed(2)}</span></div>`:''}
${sgst>0?`<div style="display:flex;justify-content:space-between;gap:24px;"><span>SGST ${sgst}%</span><span>${d.sym}${sgstAmt.toFixed(2)}</span></div>`:''}
<div style="display:flex;justify-content:space-between;gap:24px;font-size:15px;font-weight:900;border-top:2px solid #222;padding-top:4px;margin-top:4px;"><span>TOTAL</span><span>${d.sym}${d.total.toFixed(2)}</span></div>
</div>
</div>
</div>`;
    }
    const c=TYPE_COLORS[d.type]||'#37474f';const label=TYPE_LABELS[d.type]||'Invoice';
    const gstin=d.td.gstin||'';const hsn=d.td.hsn_code||'';
    const cgst=parseFloat(d.td.cgst_pct)||0;const sgst=parseFloat(d.td.sgst_pct)||0;
    const pos=d.td.place_of_supply||'';
    const cgstAmt=d.subtotal*cgst/100;const sgstAmt=d.subtotal*sgst/100;
    const items=d.items.map((it,i)=>`<tr style="background:${i%2===0?'#fafafa':'#fff'};"><td style="padding:7px 10px;font-size:12px;border-bottom:1px solid #eee;text-align:center;">${i+1}</td><td style="padding:7px 10px;font-size:12px;border-bottom:1px solid #eee;">${escHtml(it.description||'-')}</td>${hsn?`<td style="padding:7px 10px;font-size:11px;border-bottom:1px solid #eee;text-align:center;">${escHtml(hsn)}</td>`:''}<td style="padding:7px 10px;text-align:center;font-size:12px;border-bottom:1px solid #eee;">${it.qty}</td><td style="padding:7px 10px;text-align:right;font-size:12px;border-bottom:1px solid #eee;">${d.sym}${it.rate.toFixed(2)}</td><td style="padding:7px 10px;text-align:right;font-weight:700;font-size:12px;border-bottom:1px solid #eee;">${d.sym}${it.amount.toFixed(2)}</td></tr>`).join('');
    return`<div style="font-family:Arial,sans-serif;background:#fff;font-size:12px;color:#222;border:1px solid #ddd;box-shadow:0 2px 12px rgba(0,0,0,.1);">
<div style="background:${c};color:#fff;padding:20px 24px;">
<div style="display:flex;justify-content:space-between;align-items:flex-start;">
<div><div style="font-size:22px;font-weight:700;">${escHtml(d.fromName)}</div>${d.fromAddr?`<div style="font-size:10px;opacity:.85;margin-top:4px;">${escHtml(d.fromAddr).replace(/\n/g,' | ')}</div>`:''}<div style="font-size:10px;opacity:.85;">${d.fromPhone?'📞 '+escHtml(d.fromPhone)+'  ':''}${d.fromEmail?'✉ '+escHtml(d.fromEmail):''}</div>${gstin?`<div style="font-size:10px;opacity:.85;">GSTIN: ${escHtml(gstin)}</div>`:''}</div>
<div style="text-align:right;"><div style="font-size:26px;font-weight:900;letter-spacing:-1px;opacity:.9;">INVOICE</div><div style="font-size:11px;opacity:.85;"># ${escHtml(d.billNo)}</div><div style="font-size:11px;opacity:.85;">Date: ${fmtDate(d.billDate)}</div>${pos?`<div style="font-size:10px;opacity:.85;">Supply: ${escHtml(pos)}</div>`:''}<div style="font-size:10px;margin-top:4px;background:rgba(255,255,255,.2);padding:2px 8px;border-radius:4px;">${escHtml(label)}</div></div>
</div>
</div>
<div style="padding:12px 24px;background:#f7f7f7;border-bottom:2px solid ${c};">
<span style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:.05em;display:block;">Bill To</span>
<span style="font-size:14px;font-weight:700;">${escHtml(d.toName)}</span>
${d.toAddr?`<span style="font-size:11px;color:#555;display:block;">${escHtml(d.toAddr).replace(/\n/g,' | ')}</span>`:''}
${d.toPhone?`<span style="font-size:11px;color:#555;">📞 ${escHtml(d.toPhone)}</span>`:''}
${d.toEmail?`<span style="font-size:11px;color:#555;display:block;">${escHtml(d.toEmail)}</span>`:''}
</div>
<table style="width:100%;border-collapse:collapse;"><thead><tr style="background:${c};color:#fff;"><th style="padding:8px 10px;text-align:center;font-size:11px;width:30px;">#</th><th style="padding:8px 10px;text-align:left;font-size:11px;">Description</th>${hsn?`<th style="padding:8px 10px;text-align:center;font-size:11px;width:70px;">HSN/SAC</th>`:''}<th style="padding:8px 10px;text-align:center;font-size:11px;width:50px;">Qty</th><th style="padding:8px 10px;text-align:right;font-size:11px;width:80px;">Rate</th><th style="padding:8px 10px;text-align:right;font-size:11px;width:90px;">Amount</th></tr></thead>
<tbody>${items||'<tr><td colspan="6" style="padding:14px;text-align:center;color:#aaa;">No items</td></tr>'}</tbody></table>
<div style="padding:12px 24px 16px;display:flex;justify-content:flex-end;background:#fafafa;border-top:1px solid #eee;">
<div style="min-width:240px;">
<div style="display:flex;justify-content:space-between;padding:4px 0;font-size:12px;border-bottom:1px solid #eee;"><span style="color:#666;">Subtotal</span><span style="font-weight:600;">${d.sym}${d.subtotal.toFixed(2)}</span></div>
${cgst>0?`<div style="display:flex;justify-content:space-between;padding:4px 0;font-size:12px;border-bottom:1px solid #eee;"><span style="color:#666;">CGST @ ${cgst}%</span><span style="font-weight:600;">${d.sym}${cgstAmt.toFixed(2)}</span></div>`:''}
${sgst>0?`<div style="display:flex;justify-content:space-between;padding:4px 0;font-size:12px;border-bottom:1px solid #eee;"><span style="color:#666;">SGST @ ${sgst}%</span><span style="font-weight:600;">${d.sym}${sgstAmt.toFixed(2)}</span></div>`:''}
${d.taxPct>0&&cgst===0&&sgst===0?`<div style="display:flex;justify-content:space-between;padding:4px 0;font-size:12px;border-bottom:1px solid #eee;"><span style="color:#666;">Tax ${d.taxPct}%</span><span style="font-weight:600;">${d.sym}${d.taxAmt.toFixed(2)}</span></div>`:''}
${d.discount>0?`<div style="display:flex;justify-content:space-between;padding:4px 0;font-size:12px;border-bottom:1px solid #eee;"><span style="color:#666;">Discount</span><span style="font-weight:600;color:#e53935;">-${d.sym}${d.discount.toFixed(2)}</span></div>`:''}
<div style="display:flex;justify-content:space-between;padding:8px 0 4px;font-size:16px;font-weight:900;border-top:2px solid ${c};color:${c};"><span>Total</span><span>${d.sym}${d.total.toFixed(2)}</span></div>
</div>
</div>
${d.notes?`<div style="margin:0 24px 16px;background:#f5f5f5;border-radius:4px;padding:10px;font-size:11px;color:#555;border-left:3px solid ${c};"><b>Terms & Notes:</b> ${escHtml(d.notes)}</div>`:''}
<div style="background:${c};color:rgba(255,255,255,.7);padding:8px 24px;text-align:center;font-size:10px;">Thank you for your business! | BillX</div>
</div>`;}

// ─── Main dispatcher ──────────────────────────────────────────────────────────
let isCrambled=false;
function updatePreview(){
    const d=collectData();
    const label=TYPE_LABELS[d.type]||d.type;const c=TYPE_COLORS[d.type]||'#333';
    document.getElementById('previewTypeBadge').textContent=label;
    document.getElementById('previewTypeBadge').style.background=c;
    let html='';
    switch(d.group){
        case 'thermal':  html=renderThermal(d); break;
        case 'payslip':  html=renderPayslip(d); break;
        case 'fuel':     html=renderFuel(d);    break;
        case 'cab':      html=renderCab(d);     break;
        case 'official': html=renderOfficial(d);break;
        case 'medical':  html=renderMedical(d); break;
        case 'hotel':    html=renderHotel(d);   break;
        case 'gym':      html=renderGym(d);     break;
        default:         html=renderInvoice(d); break;
    }
    const wrapper=document.getElementById('billPreview');
    wrapper.style.background='transparent';wrapper.style.borderRadius='0';wrapper.style.boxShadow='none';
    wrapper.innerHTML=html;
}

// ─── Crambled toggle ──────────────────────────────────────────────────────────
function toggleCrambled(){
    isCrambled=!isCrambled;
    const pw=document.getElementById('billPreviewWrapper');
    const btn=document.getElementById('crambleBtn');
    if(isCrambled){pw.classList.add('crambled');btn.classList.add('active');}
    else{pw.classList.remove('crambled');btn.classList.remove('active');}
}

// ─── Form submit dispatcher ───────────────────────────────────────────────────
function submitBill(action){
    const chk=document.getElementById('policy_agree');
    if(chk && chk.required && !chk.checked){chk.reportValidity();chk.focus();return;}
    if(action==='download'){
        // Generate PDF from live preview first, then save silently to history
        _downloadFromPreviewThenSave();
        return;
    }
    document.getElementById('save_action').value=action;
    document.getElementById('billForm').submit();
}

async function _downloadFromPreviewThenSave(){
    const dlBtn=document.querySelector('button[onclick*="download"]');
    if(dlBtn){dlBtn.disabled=true;dlBtn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Generating…';}
    try{
        const el=document.getElementById('billPreview');
        if(window._pdfLibError||typeof html2canvas==='undefined'||typeof window.jspdf==='undefined'){
            // Libraries not loaded, fall back to server-side redirect
            document.getElementById('save_action').value='download';
            document.getElementById('billForm').submit();
            return;
        }
        const group=getGroup(document.getElementById('bill_type').value);
        const billNo=(document.getElementById('bill_number').value||'bill').replace(/[^a-zA-Z0-9._-]/g,'-');
        const typeLbl=(TYPE_LABELS[document.getElementById('bill_type').value]||'bill').replace(/[^a-zA-Z0-9._-]/g,'-');
        const filename=typeLbl+'-'+billNo+'.pdf';

        // Scroll the preview wrapper so the top of the bill is in view
        const wrapper=document.getElementById('billPreviewWrapper');
        if(wrapper) wrapper.scrollTop=0;
        // Wait for two paint frames to ensure layout is settled
        await new Promise(r=>requestAnimationFrame(()=>requestAnimationFrame(r)));

        // Measure full content dimensions (not clipped by the scroll container)
        const fullW=el.scrollWidth||el.offsetWidth;
        const fullH=el.scrollHeight||el.offsetHeight;
        const winW=Math.max(document.documentElement.scrollWidth,fullW);
        const winH=Math.max(document.documentElement.scrollHeight,fullH);

        const canvas=await html2canvas(el,{
            scale:2,useCORS:true,allowTaint:true,logging:false,backgroundColor:'#ffffff',
            scrollX:0,scrollY:0,x:0,y:0,
            width:fullW,height:fullH,
            windowWidth:winW,windowHeight:winH
        });
        const imgData=canvas.toDataURL('image/png');
        const jsPDF=window.jspdf.jsPDF;
        var pdf;
        const margin=8;
        if(group==='thermal'){
            const mmWidth=80;
            const mmHeight=(canvas.height/canvas.width)*mmWidth;
            pdf=new jsPDF({orientation:'portrait',unit:'mm',format:[mmWidth,mmHeight]});
            pdf.addImage(imgData,'PNG',0,0,mmWidth,mmHeight);
        }else{
            const pageW=210;
            const imgW=pageW-margin*2;
            const imgH=(canvas.height/canvas.width)*imgW;
            const pageH=imgH+margin*2;
            pdf=new jsPDF({orientation:'portrait',unit:'mm',format:[pageW,pageH]});
            pdf.addImage(imgData,'PNG',margin,margin,imgW,imgH);
        }
        pdf.save(filename);
        // Now save the bill silently (redirects to history)
        document.getElementById('save_action').value='save';
        document.getElementById('billForm').submit();
    }catch(e){
        console.error('PDF generation error:',e);
        // On error, fall back to server-side download
        document.getElementById('save_action').value='download';
        document.getElementById('billForm').submit();
    }
}

// ─── Wire up all live inputs ──────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded',()=>{
    addItem('',1,0);
    const liveInputs=['bill_number','bill_date','currency','from_name','from_address','from_phone','from_email','to_name','to_address','to_phone','to_email','tax_percent','discount_amount','notes'];
    liveInputs.forEach(id=>{const el=document.getElementById(id);if(!el)return;el.addEventListener(el.tagName==='SELECT'?'change':'input',updatePreview);});
    // Template style radio buttons
    document.querySelectorAll('input[name="td_template_style"]').forEach(r=>r.addEventListener('change',updatePreview));
    // Event delegation: wire up all extra-field inputs via their shared parent card
    const extraCard=document.getElementById('extraFieldsCard');
    if(extraCard){
        extraCard.addEventListener('input',updatePreview);
        extraCard.addEventListener('change',updatePreview);
    }
    // Bill type triggers extra-fields sync + preview
    const typeSelect=document.getElementById('bill_type');
    typeSelect.addEventListener('change',()=>{syncExtraFields(typeSelect.value);updatePreview();});
    syncExtraFields(typeSelect.value);
    updatePreview();
});
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" crossorigin="anonymous"
    onerror="window._pdfLibError=true"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" crossorigin="anonymous"
    onerror="window._pdfLibError=true"></script>
