using IronOcr;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Net.Http;
using System.Threading.Tasks;
using System.Windows.Forms;



namespace AutoLogin
{

    public partial class Main : Form
    {
        public List<Account> list = new List<Account>();
        public List<string> listDevice = new List<string>();
        public List<Device> devices = new List<Device>();
        public bool status = false;
        public Main()
        {
            InitializeComponent();

            listDevice = KAutoHelper.ADBHelper.GetDevices();

            listDevice.ForEach(x =>
            {
                var device = new Device();
                device.Name = x;
                device.Progress = 0 + "/40";
                device.Status = "Chưa chạy";

                devices.Add(device);
            });

            Control.CheckForIllegalCrossThreadCalls = false;


            dataGridView1.DataSource = devices;
            dataGridView1.AutoGenerateColumns = false;
            dataGridView1.Refresh();

        }

        private void btnList1_Click(object sender, EventArgs e)
        {
            foreach (var device in listDevice)
            {
                Task t = new Task(async () =>
                {
                    var a = devices.Where(x => x.Name.Equals(device)).First();

                    int i = 1;

                    while (i <= 40)
                    {
                        var screen = KAutoHelper.ADBHelper.ScreenShoot(device);

                        var x = KAutoHelper.CaptureHelper.ResizeImage(screen, 960, 560);


                        var cauhoi = KAutoHelper.CaptureHelper.CropImage(x, new System.Drawing.Rectangle(181, 86, 600, 32));
                        var dapanA = KAutoHelper.CaptureHelper.CropImage(x, new System.Drawing.Rectangle(192, 262, 168, 32));
                        var dapanB = KAutoHelper.CaptureHelper.CropImage(x, new System.Drawing.Rectangle(392, 262, 168, 32));
                        var dapanC = KAutoHelper.CaptureHelper.CropImage(x, new System.Drawing.Rectangle(592, 262, 168, 32));
                        var dapanD = KAutoHelper.CaptureHelper.CropImage(x, new System.Drawing.Rectangle(192, 302, 168, 32));

                        string cauhoitext = "";
                        string dapanAtext = "";
                        string dapanBtext = "";
                        string dapanCtext = "";
                        string dapanDtext = "";

                        var ocr = new IronTesseract();

                        ocr.Language = OcrLanguage.Vietnamese;
                        ocr.AddSecondaryLanguage(OcrLanguage.English);

                        using (var input = new OcrInput(cauhoi))
                        {
                            input.Scale(120);
                            input.Deskew();
                            OcrResult result = ocr.Read(input);
                            cauhoitext = result.Text;
                        }

                        using (var input = new OcrInput(dapanA))
                        {
                            input.Scale(120);
                            input.Deskew();   //fixes rotation and perspectiv
                            OcrResult result = ocr.Read(input);
                            dapanAtext = result.Text;
                        }

                        using (var input = new OcrInput(dapanB))
                        {
                            input.Scale(120);
                            input.Deskew();   //fixes rotation and perspectiv
                            OcrResult result = ocr.Read(input);
                            dapanBtext = result.Text;
                        }

                        using (var input = new OcrInput(dapanC))
                        {
                            input.Scale(120);
                            input.Deskew();   //fixes rotation and perspectiv
                            OcrResult result = ocr.Read(input);
                            dapanCtext = result.Text;
                        }

                        using (var input = new OcrInput(dapanD))
                        {
                            input.Scale(120);
                            input.Deskew();   //fixes rotation and perspectiv
                            OcrResult result = ocr.Read(input);
                            dapanDtext = result.Text;
                        }

                        var httpClient = new HttpClient();

                        var response = await httpClient.GetAsync("http://vannt.click/get.php?cauhoi=" + cauhoitext + "&A=" + dapanAtext + "&B=" + dapanBtext + "&C=" + dapanCtext + "&D=" + dapanDtext);

                        var answer = await response.Content.ReadAsStringAsync();

                        if (answer.Equals("1"))
                        {
                            KAutoHelper.ADBHelper.TapByPercent(device, 30.5, 49.0);
                        }
                        else if (answer.Equals("2"))
                        {
                            KAutoHelper.ADBHelper.TapByPercent(device, 49.0, 49.0);
                        }
                        else if (answer.Equals("3"))
                        {
                            KAutoHelper.ADBHelper.TapByPercent(device, 70.6, 49);
                        }
                        else
                        {
                            KAutoHelper.ADBHelper.TapByPercent(device, 28.4, 56.5);
                        }

                        Task.Delay(1000).Wait();

                        KAutoHelper.ADBHelper.TapByPercent(device, 54.4, 31.4);

                        Task.Delay(2000).Wait();

                        a.Progress = i + "/40";
                        a.Status = "Đang chạy";

                        dataGridView1.Refresh();

                        i++;

                        Task.Delay(2000).Wait();

                    }

                    a.Name = device;
                    a.Progress = (i - 1) + "/40";
                    a.Status = "Hoàn thành";

                    dataGridView1.Refresh();
                    return;

                });

                t.Start();
            }
        }



        private void button5_Click(object sender, EventArgs e)
        {
            listDevice.Clear();
            devices.Clear();
            var cmd = "adb kill-server && adb start-server";
            KAutoHelper.ADBHelper.ExecuteCMD(cmd.ToString());
            listDevice = KAutoHelper.ADBHelper.GetDevices();


            listDevice.ForEach(x =>
            {
                var device = new Device();
                device.Name = x;
                device.Progress = 0 + "/40";
                device.Status = "Chưa chạy";

                devices.Add(device);
            });
            dataGridView1.AutoGenerateColumns = false;

        }
    }

    public class Device
    {
        public string Name { get; set; }
        public string Progress { get; set; }
        public string Status { get; set; }
    }

}