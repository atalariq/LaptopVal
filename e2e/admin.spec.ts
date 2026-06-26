import { test, expect } from "@playwright/test";

const ADMIN_USER = process.env.ADMIN_USERNAME ?? "admin";
const ADMIN_PASS = process.env.ADMIN_PASSWORD ?? "admin123";

test.describe("admin auth", () => {
  test("redirects unauthenticated access to login", async ({ page }) => {
    await page.goto("/admin");
    await expect(page).toHaveURL(/\/login/);
    await expect(
      page.getByRole("heading", { name: "Login Admin" }),
    ).toBeVisible();
  });

  test("rejects bad credentials", async ({ page }) => {
    await page.goto("/login");
    await page.getByLabel("Username").fill(ADMIN_USER);
    await page.getByLabel("Password").fill("wrong-password");
    await page.getByRole("button", { name: "Masuk" }).click();
    await expect(page.getByText("Username atau password salah.")).toBeVisible();
  });

  test("logs in, reaches dashboard, and logs out", async ({ page }) => {
    await page.goto("/login");
    await page.getByLabel("Username").fill(ADMIN_USER);
    await page.getByLabel("Password").fill(ADMIN_PASS);
    await page.getByRole("button", { name: "Masuk" }).click();

    await expect(page).toHaveURL(/\/admin$/);
    await expect(page.getByRole("link", { name: "Dashboard" })).toBeVisible();

    await page.getByRole("button", { name: "Logout" }).click();
    // Logout clears the session; admin is no longer reachable.
    await page.goto("/admin");
    await expect(page).toHaveURL(/\/login/);
  });
});

test.describe("admin cpu management", () => {
  test("admin can add and remove a CPU", async ({ page }) => {
    await page.goto("/login");
    await page.getByLabel("Username").fill(ADMIN_USER);
    await page.getByLabel("Password").fill(ADMIN_PASS);
    await page.getByRole("button", { name: "Masuk" }).click();
    await expect(page).toHaveURL(/\/admin$/);

    await page.goto("/admin/cpus");
    await page
      .locator('form[action="?/create"] input[name="name"]')
      .fill("Test CPU ZZ");
    await page
      .locator('form[action="?/create"] input[name="benchmark"]')
      .fill("12345");
    await page.getByRole("button", { name: "Tambah" }).click();
    // Success flash confirms the row was created; name renders in an <input value>
    await expect(page.getByText("CPU ditambahkan.")).toBeVisible();

    page.on("dialog", (d) => d.accept());
    const row = page.locator("tr").filter({
      has: page.locator('input[name="name"][value="Test CPU ZZ"]'),
    });
    await row.getByRole("button", { name: "Hapus" }).click();
    await expect(page.getByText("CPU dihapus.")).toBeVisible();
  });
});
